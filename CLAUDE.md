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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/boost (BOOST) - v2
- laravel/framework (LARAVEL) - v13
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
