# AI CRM + Telegram для ресторанов

SaaS-платформа: управление бронированиями через Telegram-бота с AI-обработкой сообщений,
CRM для клиентов, аналитика, биллинг. Каждый ресторан — изолированный тенант со своим ботом.

---

## Технологический стек

| Компонент      | Технология                          |
|----------------|-------------------------------------|
| Backend        | Laravel (последняя стабильная)      |
| PHP            | 8.3                                 |
| База данных    | PostgreSQL                          |
| Кэш / Очереди  | Redis                               |
| WebSockets     | Laravel Reverb                      |
| Инфраструктура | Docker (nginx, php, postgres, redis, supervisor, reverb) |

### Пакеты

| Пакет                        | Назначение                        |
|------------------------------|-----------------------------------|
| nutgram                      | Telegram-бот (FSM, Laravel-friendly) |
| spatie/laravel-model-states  | FSM статусов брони                |
| spatie/laravel-data          | DTO (валидация + трансформация)   |
| Laravel Sanctum              | Аутентификация + API-ключи        |

---

## Архитектура

### Принципы

- **DDD-lite** — каждый домен изолирован, имеет свои routes, controllers, services
- **Action pattern** — вся бизнес-логика в Actions
- **DTO** — никаких голых массивов, только spatie/laravel-data
- **Events + Queues** — асинхронная обработка
- **API-first** — версионирование `/api/v1/`

### Запрещено

- ❌ Fat controllers
- ❌ Бизнес-логика в моделях
- ❌ Helper hell
- ❌ Service class dump
- ❌ Repository overengineering

---

## Namespace'ы и структура папок

```
app/
├── Domains/
│   ├── Booking/
│   │   ├── Actions/
│   │   ├── DTO/
│   │   ├── Events/
│   │   ├── Exceptions/
│   │   ├── Jobs/
│   │   ├── Listeners/
│   │   ├── Models/
│   │   ├── Policies/
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── States/
│   ├── Restaurant/
│   ├── Telegram/
│   ├── AI/
│   ├── Analytics/
│   ├── Notification/
│   └── User/
├── Infrastructure/
│   ├── CurrentRestaurant.php
│   └── RestaurantScope.php
└── Shared/
    ├── Concerns/
    │   └── HasUuid.php
    └── Exceptions/
        └── DomainException.php
```

Namespace строго следует структуре: `App\Domains\Booking\Actions\CreateBookingAction`

---

## Соглашения по именованию

| Тип       | Суффикс    | Пример                         |
|-----------|------------|--------------------------------|
| Action    | Action     | CreateBookingAction            |
| DTO       | DTO        | CreateBookingDTO               |
| Event     | Event      | BookingCreatedEvent            |
| Listener  | Listener   | SendBookingConfirmationListener|
| Job       | Job        | ProcessBookingReminderJob      |
| Policy    | Policy     | BookingPolicy                  |
| Request   | Request    | CreateBookingRequest           |
| Resource  | Resource   | BookingResource                |
| Exception | Exception  | BookingNotFoundException       |
| Enum      | без суффикса | BookingStatus                |
| State     | State      | PendingState                   |

---

## Скелеты файлов

### Action
```php
// Зависимости ТОЛЬКО через handle(), не через конструктор
class CreateBookingAction
{
    public function handle(CreateBookingDTO $dto): Booking
    {
        // логика
    }
}
```

### DTO
```php
// extends Data из spatie/laravel-data, readonly свойства
class CreateBookingDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly int    $guestsCount,
        public readonly Carbon $bookingStart,
        public readonly Carbon $bookingEnd,
    ) {}
}
```

### Event
```php
// Только ID, без модели — безопасно для очередей
class BookingCreatedEvent
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $restaurantId,
    ) {}
}
```

### Job
```php
// Только ID в конструкторе, модель подгружается в handle()
class ProcessBookingReminderJob implements ShouldQueue
{
    public function __construct(
        public readonly string $bookingId,
    ) {}

    public function handle(): void
    {
        $booking = Booking::findOrFail($this->bookingId);
        // логика
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Job failed: {$e->getMessage()}", ['bookingId' => $this->bookingId]);
    }
}
```

### Listener
```php
// Listener — только диспетчер. Логика в Action, не в Listener
class SendBookingConfirmationListener
{
    public function handle(BookingCreatedEvent $event): void
    {
        app(SendBookingConfirmationAction::class)->handle($event->bookingId);
    }
}
```

### Request
```php
// Метод toDTO() внутри Request — контроллер остаётся тонким
class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'guests_count'  => ['required', 'integer', 'min:1'],
            'booking_start' => ['required', 'date'],
            'booking_end'   => ['required', 'date', 'after:booking_start'],
        ];
    }

    public function toDTO(): CreateBookingDTO
    {
        return CreateBookingDTO::from($this->validated());
    }
}
```

### Controller (тонкий)
```php
public function store(CreateBookingRequest $request): JsonResponse
{
    $booking = app(CreateBookingAction::class)->handle($request->toDTO());
    return BookingResource::make($booking)->response();
}
```

### Resource
```php
// Статус как объект {value, label}
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'guests_count'  => $this->guests_count,
            'booking_start' => $this->booking_start,
            'customer'      => CustomerResource::make($this->whenLoaded('customer')),
        ];
    }
}
```

### Exception
```php
// HTTP-коды держим в Handler, не в исключениях
class BookingNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self("Booking [{$id}] not found.");
    }
}
```

---

## Мультитенантность

```php
// CurrentRestaurant::id() — единственное место резолвинга
CurrentRestaurant::id() // → null для super_admin (видит все данные)
CurrentRestaurant::id() // → $user->restaurant_id для остальных ролей

// RestaurantScope применяет WHERE только если id не null
// Все модели с restaurant_id обязаны использовать RestaurantScope
```

**Правило:** проверка `restaurant_id` обязательна в каждом методе Policy.

---

## Мультибот

- Каждый ресторан — свой Telegram-бот, токен в `restaurant_bots`
- Единый endpoint: `POST /webhook/telegram/{restaurant_id}`
- Webhook регистрируется автоматически при создании ресторана через Events/Listeners
- Webhook удаляется автоматически при удалении ресторана

---

## Роли и доступ

| Действие                   | super_admin | owner | manager |
|----------------------------|:-----------:|:-----:|:-------:|
| Управление рестораном      | ✅          | ✅    | ❌      |
| Управление столами/этажами | ✅          | ✅    | ❌      |
| Создание брони             | ✅          | ✅    | ✅      |
| Подтверждение/отмена брони | ✅          | ✅    | ✅      |
| Переназначение стола       | ✅          | ✅    | ✅      |
| Просмотр броней            | ✅          | ✅    | ✅      |
| Управление пользователями  | ✅          | ✅    | ❌      |
| Просмотр аналитики         | ✅          | ✅    | ✅      |
| Управление ботом           | ✅          | ✅    | ❌      |

- `super_admin` проходит все проверки через `before()` в каждой Policy
- `owner` управляет только своим рестораном

---

## Очереди

| Очередь | Tries | Backoff | Воркеры |
|---------|-------|---------|---------|
| high    | 5     | 10 сек  | 3       |
| default | 3     | 30 сек  | 2       |
| low     | 3     | 60 сек  | 1       |

| Job                          | Очередь | Домен       |
|------------------------------|---------|-------------|
| SendBookingConfirmationJob   | high    | Booking     |
| SendTelegramMessageJob       | high    | Telegram    |
| SendBookingReminderJob       | default | Booking     |
| AutoCancelExpiredBookingsJob | default | Booking     |
| SendNotificationJob          | default | Notification|
| ProcessAiRequestJob          | low     | AI          |
| RecordAnalyticsEventJob      | low     | Analytics   |

---

## States (FSM) — Booking

```
pending → confirmed → completed
pending → cancelled
confirmed → cancelled
confirmed → no_show
```

- `CancelledState`, `CompletedState`, `NoShowState` — финальные, переходов нет
- Реализация через `spatie/laravel-model-states`

---

## FSM Telegram-бота

```
Бронирование:
idle → awaiting_date → awaiting_time → awaiting_guests
     → awaiting_name → awaiting_phone → awaiting_comment
     → awaiting_confirm → idle (бронь создана, статус pending)

Отмена:
idle → awaiting_cancel → awaiting_cancel_confirm → idle
```

- 3 неудачных попытки ввода → `ResetConversationAction` → idle, payload очищается
- `/start`, `/cancel`, `/help` работают в **любом** состоянии

---

## API

- Версионирование: `/api/v1/`
- Пагинация: курсорная (лучше для UUID)
- Rate limiting: 60 req/min (auth), 30 req/min (public)

### Формат ответа

```json
// Успех
{ "data": {}, "message": "OK", "meta": {} }

// Ошибка
{ "message": "Booking not found", "errors": {}, "code": 404 }
```

### API Abilities (Sanctum)

| Ability      | Доступ                     |
|--------------|----------------------------|
| read         | только чтение              |
| write        | чтение и запись            |
| bookings     | только операции с бронями  |
| full_access  | полный доступ              |

---

## БД: важные детали

- Все PK — UUID
- Все timestamps — `timestampTz` (с timezone)
- `time_slot_configs` — только конфигурация, слоты вычисляются динамически
- Защита от двойного бронирования — PostgreSQL exclusion constraint:

```sql
ALTER TABLE bookings ADD CONSTRAINT no_overlapping_bookings
EXCLUDE USING gist (
    table_id WITH =,
    tstzrange(booking_start, booking_end) WITH &&
) WHERE (status IN ('pending', 'confirmed'));
```

---

## Биллинг

Gateway pattern для мультивалютности. Бизнес-код работает только с `PaymentGatewayInterface`.
`StripeGateway` — конкретная реализация.

---

## Команды

```bash
# Запуск окружения
docker compose up -d

# Миграции
php artisan migrate

# Тесты
php artisan test

# Очереди (через supervisor в Docker)
php artisan queue:work --queue=high --tries=5
php artisan queue:work --queue=default --tries=3
php artisan queue:work --queue=low --tries=3

# Scheduler
php artisan schedule:run
```

---

## Фазы разработки

| Фаза | Что делается                        | Зависимости |
|------|-------------------------------------|-------------|
| 0    | Инфраструктура, Docker, базовые классы | —        |
| 1    | Миграции БД (17 миграций)           | Фаза 0      |
| 2    | Models + Enums + States             | Фазы 0-1    |
| 3    | DTO                                 | Фазы 0-2    |
| 4    | Booking Engine (Actions, Allocator, SlotCalculator) | Фазы 0-3 |
| 5    | Telegram Webhook + Nutgram          | Фазы 0-4    |
| 6    | FSM Conversations (диалоги бота)    | Фазы 0-5    |
| 7    | Queue System (Jobs, Scheduler)      | Фазы 0-6    |
| 8    | Policies                            | Фазы 0-7    |
| 9    | API Resources + Controllers         | Фазы 0-8    |

**Текущий статус:** `[УКАЖИ ЗАВЕРШЁННЫЕ ФАЗЫ — например: Фазы 0-2 завершены]`

---

## Правила работы с AI (для Claude)

1. Генерируй **по одному компоненту** — не всю фазу сразу
2. Перед генерацией уточни зависимости и список уже созданных файлов
3. Бизнес-логика — только в Actions, не в моделях и не в контроллерах
4. Каждый новый файл должен иметь корректный namespace по структуре папок
5. Для сложных фаз (4, 6) — сначала `/plan`, потом реализация
6. После каждой фазы — `git commit` с понятным сообщением
