# API Reference — Restaurant CRM

Базовый URL: `https://{host}/api/v1`

## Аутентификация

API использует Laravel Sanctum (Bearer-токены). Токен передаётся в заголовке:

```
Authorization: Bearer {token}
```

Все эндпоинты, кроме `POST /auth/login`, требуют авторизации.

### Rate Limiting

| Группа         | Лимит       |
|----------------|-------------|
| Публичные      | 30 запр/мин |
| Авторизованные | 60 запр/мин |

---

## Формат ответов

**Успех:**
```json
{
  "data": {},
  "message": "OK"
}
```

**Ошибка валидации (422):**
```json
{
  "message": "The guests count field is required.",
  "errors": {
    "guests_count": ["The guests count field is required."]
  }
}
```

**Ошибка (4xx / 5xx):**
```json
{
  "message": "Booking [uuid] not found."
}
```

**Пагинация (курсорная):**
```json
{
  "data": [...],
  "links": {
    "first": null,
    "last": null,
    "prev": null,
    "next": "https://{host}/api/v1/bookings?cursor=..."
  },
  "meta": {
    "per_page": 15,
    "next_cursor": "...",
    "prev_cursor": null
  }
}
```

---

## Auth

### POST /auth/login

Получить токен доступа. Аутентификация по логину и паролю.

| Поле     | Тип    | Обяз. | Описание |
|----------|--------|-------|----------|
| login    | string | да    | Уникальный логин пользователя |
| password | string | да    | |
| ability  | string | нет   | `read`, `write`, `bookings`, `full_access` (по умолч. `full_access`) |

**Пример запроса:**
```json
{
  "login": "ivan_ivanov",
  "password": "secret"
}
```

**Ответ 200:**
```json
{
  "data": {
    "token": "1|abcdef123456..."
  },
  "message": "OK"
}
```

**Ответ 401** — неверный логин или пароль.

---

### POST /auth/logout

Отозвать текущий токен.

**Ответ 200:**
```json
{
  "message": "Logged out."
}
```

---

## Bookings

### Объект Booking

```json
{
  "id": "uuid",
  "status": {
    "value": "pending",
    "label": "Ожидает"
  },
  "source": {
    "value": "web",
    "label": "Web"
  },
  "guests_count": 4,
  "booking_start": "2026-06-01T18:00:00+03:00",
  "booking_end": "2026-06-01T20:00:00+03:00",
  "comment": "Аллергия на орехи",
  "customer": { /* CustomerResource */ },
  "table": {
    "id": "uuid",
    "number": 5,
    "capacity": 6
  },
  "notes": [ /* BookingNoteResource[] */ ],
  "created_at": "2026-05-30T10:00:00+03:00",
  "updated_at": "2026-05-30T10:00:00+03:00"
}
```

**Статусы бронирования:**

| Значение  | Описание      | Переходы                                    |
|-----------|---------------|---------------------------------------------|
| pending   | Ожидает       | → confirmed, → cancelled                   |
| confirmed | Подтверждена  | → completed, → cancelled, → no_show        |
| completed | Завершена     | финальный                                   |
| cancelled | Отменена      | финальный                                   |
| no_show   | Не явился     | финальный                                   |

**Источники (source):**

`web`, `telegram`, `phone`, `walkin`

---

### GET /bookings

Список бронирований (курсорная пагинация, 15 на страницу).

**Права:** owner, manager

**Ответ 200:** массив `Booking` с пагинацией.

---

### POST /bookings

Создать бронирование. Создаётся в статусе `pending`.

**Права:** owner, manager

**Тело запроса:**

| Поле          | Тип     | Обяз. | Описание                          |
|---------------|---------|-------|-----------------------------------|
| guests_count  | integer | да    | 1–100                             |
| booking_start | datetime | да   | После текущего момента            |
| booking_end   | datetime | да   | После `booking_start`             |
| table_id      | uuid    | нет   | Конкретный стол                   |
| customer_id   | uuid    | нет   | Существующий клиент               |
| comment       | string  | нет   | До 500 символов                   |
| source        | string  | нет   | `web`, `telegram`, `phone`, `walkin` (по умолч. `web`) |

**Пример:**
```json
{
  "guests_count": 4,
  "booking_start": "2026-06-01T18:00:00",
  "booking_end": "2026-06-01T20:00:00",
  "table_id": "uuid-стола",
  "comment": "Столик у окна"
}
```

**Ответ 201:** объект `Booking`.

---

### GET /bookings/{id}

Получить бронирование. Загружает `customer`, `table`, `notes`.

**Права:** owner, manager (только своего ресторана)

**Ответ 200:** объект `Booking`.

---

### PATCH /bookings/{id}

Обновить бронирование. Все поля опциональны.

**Права:** owner, manager

**Тело запроса:**

| Поле          | Тип      | Описание                   |
|---------------|----------|----------------------------|
| guests_count  | integer  | 1–100                      |
| booking_start | datetime |                            |
| booking_end   | datetime | После `booking_start`      |
| table_id      | uuid\|null | `null` — убрать стол    |
| comment       | string\|null |                        |

**Ответ 200:** обновлённый объект `Booking`.

---

### POST /bookings/{id}/confirm

Подтвердить бронирование (`pending` → `confirmed`).

**Права:** owner, manager

Тело запроса не требуется.

**Ответ 200:** объект `Booking` со статусом `confirmed`.

---

### POST /bookings/{id}/cancel

Отменить бронирование (`pending`/`confirmed` → `cancelled`).

**Права:** owner, manager

**Тело запроса:**

| Поле   | Тип    | Обяз. | Описание           |
|--------|--------|-------|--------------------|
| reason | string | нет   | Причина, до 500 символов |

**Ответ 200:** объект `Booking` со статусом `cancelled`.

---

### POST /bookings/{id}/complete

Завершить бронирование (`confirmed` → `completed`).

**Права:** owner, manager

Тело запроса не требуется.

**Ответ 200:** объект `Booking` со статусом `completed`.

---

### POST /bookings/{id}/no-show

Отметить неявку (`confirmed` → `no_show`).

**Права:** owner, manager

Тело запроса не требуется.

**Ответ 200:** объект `Booking` со статусом `no_show`.

---

## Customers

### Объект Customer

```json
{
  "id": "uuid",
  "name": "Иван Петров",
  "phone": "+79991234567",
  "email": "ivan@example.com",
  "telegram_id": 123456789,
  "telegram_username": "ivanpetrov",
  "preferences": { "seating": "window", "dietary": "vegetarian" },
  "notes": "Постоянный гость, любит тихие столики",
  "created_at": "2026-05-30T10:00:00+03:00"
}
```

---

### GET /customers

Список клиентов (курсорная пагинация, 15 на страницу).

**Права:** owner, manager

**Ответ 200:** массив `Customer` с пагинацией.

---

### POST /customers

Создать клиента.

**Права:** owner, manager

**Тело запроса:**

| Поле              | Тип     | Обяз. | Описание          |
|-------------------|---------|-------|-------------------|
| name              | string  | да    | До 200 символов   |
| phone             | string  | нет   | До 20 символов    |
| email             | string  | нет   | Email             |
| telegram_id       | integer | нет   | Telegram user ID  |
| telegram_username | string  | нет   | До 100 символов   |

**Ответ 201:** объект `Customer`.

---

### GET /customers/{id}

Получить клиента.

**Права:** owner, manager (только своего ресторана)

**Ответ 200:** объект `Customer`.

---

### PATCH /customers/{id}

Обновить клиента. Все поля опциональны.

**Права:** owner, manager

**Тело запроса:**

| Поле         | Тип          | Описание                            |
|--------------|--------------|-------------------------------------|
| name         | string       | До 200 символов                     |
| phone        | string\|null |                                     |
| email        | string\|null |                                     |
| preferences  | object\|null | Произвольный JSON                   |
| notes        | string\|null | До 1000 символов                    |

**Ответ 200:** обновлённый объект `Customer`.

---

## Restaurant

### Объект Restaurant

```json
{
  "id": "uuid",
  "name": "My Restaurant",
  "slug": "my-restaurant",
  "timezone": "Europe/Moscow",
  "phone": "+74951234567",
  "address": "Москва, ул. Пушкина, 1",
  "settings": {},
  "is_active": true,
  "created_at": "2026-01-01T00:00:00+03:00"
}
```

---

### GET /restaurant

Получить данные своего ресторана.

**Права:** owner, manager

**Ответ 200:** объект `Restaurant`.

---

### PATCH /restaurant

Обновить данные ресторана. Все поля опциональны.

**Права:** owner

**Тело запроса:**

| Поле      | Тип          | Описание                              |
|-----------|--------------|---------------------------------------|
| name      | string       | До 200 символов                       |
| timezone  | string       | Валидный timezone (напр. `Europe/Moscow`) |
| phone     | string\|null |                                       |
| address   | string\|null | До 500 символов                       |
| settings  | object\|null | Произвольные настройки                |
| is_active | boolean      |                                       |

**Ответ 200:** обновлённый объект `Restaurant`.

---

### GET /restaurant/floors

Список этажей ресторана с вложенными столами.

**Права:** owner, manager

**Ответ 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Основной зал",
      "sort_order": 1,
      "tables": [
        {
          "id": "uuid",
          "number": 1,
          "capacity": 4,
          "min_capacity": 1,
          "is_active": true,
          "floor_id": "uuid"
        }
      ]
    }
  ]
}
```

---

### GET /restaurant/time-slot-configs

Конфигурация временны́х слотов по дням недели.

**Права:** owner, manager

**Ответ 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "day_of_week": 1,
      "open_time": "11:00",
      "close_time": "23:00",
      "slot_duration": 30,
      "booking_duration": 120,
      "is_day_off": false
    }
  ]
}
```

`day_of_week`: 1 = Понедельник, …, 7 = Воскресенье.

---

## Users (управление пользователями ресторана)

> Доступно: `super_admin` (через `before()` в политике) и `owner` своего ресторана.

### Объект User

```json
{
  "id": "uuid",
  "name": "Иван Иванов",
  "login": "ivan_ivanov",
  "email": "ivan@example.com",
  "role": "manager",
  "role_label": "Менеджер"
}
```

**Роли:**

| Значение  | Описание |
|-----------|----------|
| `owner`   | Владелец — полный доступ к ресторану |
| `manager` | Менеджер — работа с бронями и клиентами |

---

### GET /restaurants/{restaurantId}/users

Список пользователей ресторана.

**Ответ 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Иван Иванов",
      "login": "ivan_ivanov",
      "email": "ivan@example.com",
      "role": "manager",
      "role_label": "Менеджер"
    }
  ]
}
```

---

### POST /restaurants/{restaurantId}/users

Создать пользователя и привязать к ресторану.

**Тело запроса:**

| Поле     | Тип    | Обяз. | Описание                          |
|----------|--------|-------|-----------------------------------|
| name     | string | да    | До 255 символов                   |
| login    | string | да    | Уникальный логин, до 255 символов |
| email    | string | нет   | Email, уникальный в системе       |
| role     | string | да    | `owner` или `manager`             |
| password | string | да    | Минимум 8 символов                |

**Пример:**
```json
{
  "name": "Иван Иванов",
  "login": "ivan_ivanov",
  "role": "manager",
  "password": "securepass"
}
```

**Ответ 201:**
```json
{
  "data": {
    "id": "uuid",
    "name": "Иван Иванов",
    "login": "ivan_ivanov",
    "email": null,
    "role": "manager",
    "role_label": "Менеджер"
  },
  "message": "Пользователь создан."
}
```

**Ответ 422** — если логин или email уже заняты, или пароль < 8 символов.

---

### DELETE /restaurants/{restaurantId}/users/{userId}

Удалить пользователя из ресторана.

**Ответ 200:**
```json
{
  "message": "Пользователь удалён."
}
```

**Ответ 403** — если пользователь не принадлежит указанному ресторану.

---

## Slots

### GET /slots

Доступные временны́е слоты для бронирования на конкретную дату.

**Права:** owner, manager

**Query параметры:**

| Параметр         | Тип     | Обяз. | Описание                       |
|------------------|---------|-------|--------------------------------|
| date             | string  | да    | Формат `Y-m-d` (напр. `2026-06-01`) |
| guests_count     | integer | да    | 1–100                          |
| duration_minutes | integer | нет   | 30–480, по умолчанию 120       |

**Пример запроса:**
```
GET /api/v1/slots?date=2026-06-01&guests_count=4&duration_minutes=90
```

**Ответ 200:**
```json
{
  "data": [
    "2026-06-01 11:00:00",
    "2026-06-01 11:30:00",
    "2026-06-01 12:00:00"
  ],
  "meta": {
    "count": 3
  }
}
```

---

## Права доступа

| Эндпоинт                      | super_admin | owner | manager |
|-------------------------------|:-----------:|:-----:|:-------:|
| POST /auth/login              | ✅          | ✅    | ✅      |
| GET /bookings                 | ✅          | ✅    | ✅      |
| POST /bookings                | ✅          | ✅    | ✅      |
| GET /bookings/{id}            | ✅          | ✅    | ✅      |
| PATCH /bookings/{id}          | ✅          | ✅    | ✅      |
| POST /bookings/{id}/confirm   | ✅          | ✅    | ✅      |
| POST /bookings/{id}/cancel    | ✅          | ✅    | ✅      |
| POST /bookings/{id}/complete  | ✅          | ✅    | ✅      |
| POST /bookings/{id}/no-show   | ✅          | ✅    | ✅      |
| GET /customers                | ✅          | ✅    | ✅      |
| POST /customers               | ✅          | ✅    | ✅      |
| GET /customers/{id}           | ✅          | ✅    | ✅      |
| PATCH /customers/{id}         | ✅          | ✅    | ✅      |
| GET /restaurant               | ✅          | ✅    | ✅      |
| PATCH /restaurant             | ✅          | ✅    | ❌      |
| GET /restaurant/floors        | ✅          | ✅    | ✅      |
| GET /restaurant/time-slot-configs | ✅      | ✅    | ✅      |
| GET /slots                    | ✅          | ✅    | ✅      |
| GET /restaurants/{id}/users   | ✅          | ✅    | ❌      |
| POST /restaurants/{id}/users  | ✅          | ✅    | ❌      |
| DELETE /restaurants/{id}/users/{userId} | ✅ | ✅  | ❌      |

---

## HTTP коды

| Код | Ситуация                                    |
|-----|---------------------------------------------|
| 200 | Успех                                       |
| 201 | Ресурс создан                               |
| 401 | Не авторизован / неверный токен             |
| 403 | Нет прав на действие                        |
| 404 | Ресурс не найден                            |
| 422 | Ошибка валидации                            |
| 429 | Превышен лимит запросов                     |
| 500 | Внутренняя ошибка сервера                   |
