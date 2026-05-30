<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Брони — {{ $restaurant['name'] ?? 'Dashboard' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .badge-pending   { @apply bg-yellow-50 text-yellow-700 border-yellow-200; }
        .badge-confirmed { @apply bg-blue-50 text-blue-700 border-blue-200; }
        .badge-completed { @apply bg-green-50 text-green-700 border-green-200; }
        .badge-cancelled { @apply bg-red-50 text-red-600 border-red-200; }
        .badge-no_show   { @apply bg-gray-100 text-gray-500 border-gray-200; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen"
      x-data="dashboard('{{ $apiBase }}', '{{ $token }}', '{{ $restaurant['timezone'] ?? 'UTC' }}')"
      x-init="init()">

{{-- ═══════ HEADER ═══════ --}}
<header class="bg-white border-b border-gray-100 sticky top-0 z-20 shadow-sm">
    <div class="max-w-6xl mx-auto px-5 h-14 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="font-bold text-gray-900">{{ $restaurant['name'] ?? 'CRM' }}</span>
            <span class="text-gray-200">|</span>
            <nav class="flex gap-3 text-sm">
                <a href="/admin/restaurants" class="text-gray-500 hover:text-gray-800">Настройки</a>
                @php
                    $slug = \App\Domains\Restaurant\Models\Restaurant::where('name', $restaurant['name'] ?? '')->value('slug') ?? 'test-restaurant';
                @endphp
                <a href="/book/{{ $slug }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">Страница бронирования ↗</a>
            </nav>
        </div>
        <span class="text-xs text-gray-400">{{ now()->setTimezone($restaurant['timezone'] ?? 'UTC')->format('d.m.Y · H:i') }}</span>
    </div>
</header>

{{-- ═══════ DATE NAV ═══════ --}}
<div class="bg-white border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-5 py-3 flex flex-wrap items-center gap-3">

        {{-- Arrows + input --}}
        <div class="flex items-center gap-2">
            <button @click="shiftDay(-1)" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <input type="date" x-model="date" @change="reload()"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <button @click="shiftDay(1)" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <button @click="goToday()"
                :class="date === todayStr ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition">
            Сегодня
        </button>

        <span class="text-sm font-medium text-gray-700" x-text="humanDate(date)"></span>

        {{-- Stats chips --}}
        <div class="ml-auto flex flex-wrap gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-medium">
                Всего: <span x-text="bookings.length" class="font-bold"></span>
            </span>
            <template x-if="count('pending') > 0">
                <span class="px-2.5 py-1 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200 font-medium">
                    Ожидают: <span x-text="count('pending')" class="font-bold"></span>
                </span>
            </template>
            <template x-if="count('confirmed') > 0">
                <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-medium">
                    Подтв.: <span x-text="count('confirmed')" class="font-bold"></span>
                </span>
            </template>
            <template x-if="count('completed') > 0">
                <span class="px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 font-medium">
                    Завершено: <span x-text="count('completed')" class="font-bold"></span>
                </span>
            </template>
        </div>
    </div>
</div>

{{-- ═══════ BOOKINGS ═══════ --}}
<main class="max-w-6xl mx-auto px-5 py-5">

    {{-- Loading --}}
    <div x-show="loading" class="flex flex-col items-center justify-center py-20 text-gray-400 gap-3">
        <svg class="animate-spin w-7 h-7" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <span class="text-sm">Загружаем брони…</span>
    </div>

    {{-- Error --}}
    <div x-show="!loading && error"
         class="bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm mb-4"
         x-text="error"></div>

    {{-- Empty --}}
    <div x-show="!loading && !error && bookings.length === 0"
         class="flex flex-col items-center justify-center py-20 text-gray-400 gap-2">
        <span class="text-5xl">📅</span>
        <p class="text-base font-medium mt-2">Нет бронирований на эту дату</p>
        <p class="text-sm">Выберите другую дату или <a href="/book/{{ $slug }}" class="text-indigo-500 hover:underline">создайте бронь</a></p>
    </div>

    {{-- List --}}
    <div x-show="!loading && bookings.length > 0" class="space-y-2">
        <template x-for="b in bookings" :key="b.id">
            <div class="bg-white rounded-xl border border-gray-200 hover:border-gray-300 transition-all"
                 :class="{ 'opacity-60': ['cancelled','completed','no_show'].includes(b.status.value) }">
                <div class="p-4 flex items-center gap-4">

                    {{-- Time --}}
                    <div class="w-14 text-center flex-shrink-0">
                        <p class="text-base font-bold text-gray-900 leading-none" x-text="hm(b.booking_start)"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="hm(b.booking_end)"></p>
                    </div>

                    {{-- Table badge --}}
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-gray-100 flex flex-col items-center justify-center text-xs">
                        <span class="font-bold text-gray-800 text-sm" x-text="b.table ? b.table.number : '?'"></span>
                        <span class="text-gray-400 leading-none" style="font-size:9px">стол</span>
                    </div>

                    {{-- Guests --}}
                    <div class="w-8 text-center flex-shrink-0">
                        <span class="text-sm font-semibold text-gray-700" x-text="b.guests_count"></span>
                        <p class="text-gray-400 leading-none" style="font-size:9px">гост.</p>
                    </div>

                    {{-- Customer --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate leading-tight"
                           x-text="b.customer?.name || '—'"></p>
                        <p class="text-sm text-gray-500 leading-tight" x-text="b.customer?.phone || ''"></p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate" x-show="b.comment" x-text="b.comment"></p>
                    </div>

                    {{-- Status --}}
                    <div class="flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                              :class="{
                                  'bg-yellow-50 text-yellow-700 border-yellow-200': b.status.value === 'pending',
                                  'bg-blue-50 text-blue-700 border-blue-200': b.status.value === 'confirmed',
                                  'bg-green-50 text-green-700 border-green-200': b.status.value === 'completed',
                                  'bg-red-50 text-red-600 border-red-200': b.status.value === 'cancelled',
                                  'bg-gray-100 text-gray-500 border-gray-200': b.status.value === 'no_show',
                              }"
                              x-text="b.status.label"></span>
                    </div>

                    {{-- Source + capacity --}}
                    <div class="hidden md:block text-right text-xs text-gray-400 flex-shrink-0 w-20">
                        <span x-text="b.source?.label"></span>
                        <br x-show="b.table">
                        <span x-show="b.table" x-text="'до ' + b.table?.capacity + ' мест'"></span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-1.5 flex-wrap justify-end">
                        <button x-show="b.status.value === 'pending'"
                                @click="act(b, 'confirm')"
                                class="btn-sm bg-blue-600 hover:bg-blue-700 text-white">
                            Подтвердить
                        </button>
                        <button x-show="b.status.value === 'confirmed'"
                                @click="act(b, 'complete')"
                                class="btn-sm bg-green-600 hover:bg-green-700 text-white">
                            Завершить
                        </button>
                        <button x-show="b.status.value === 'confirmed'"
                                @click="act(b, 'no-show')"
                                class="btn-sm bg-gray-500 hover:bg-gray-600 text-white">
                            Не явился
                        </button>
                        <button x-show="['pending','confirmed'].includes(b.status.value)"
                                @click="cancelOpen(b)"
                                class="btn-sm bg-red-50 hover:bg-red-100 text-red-600 border border-red-200">
                            Отмена
                        </button>
                        <button x-show="['pending','confirmed'].includes(b.status.value)"
                                @click="editOpen(b)"
                                class="btn-sm bg-gray-100 hover:bg-gray-200 text-gray-600">
                            ✏️
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</main>

{{-- ═══════ CANCEL MODAL ═══════ --}}
<template x-teleport="body">
    <div x-show="cm.open" x-cloak
         class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center z-50 p-4"
         @keydown.escape.window="cm.open = false" @click.self="cm.open = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 class="font-semibold text-lg mb-1">Отменить бронирование</h3>
            <p class="text-sm text-gray-500 mb-4"
               x-text="cm.booking ? hm(cm.booking.booking_start) + ' · ' + (cm.booking.customer?.name || '—') : ''"></p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Причина (необязательно)</label>
                <input x-model="cm.reason" type="text" placeholder="Клиент отменил…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div class="flex gap-2">
                <button @click="cancelConfirm()" :disabled="cm.loading"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium transition disabled:opacity-40">
                    <span x-show="!cm.loading">Отменить бронь</span>
                    <span x-show="cm.loading">Отменяем…</span>
                </button>
                <button @click="cm.open = false"
                        class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">
                    Назад
                </button>
            </div>
        </div>
    </div>
</template>

{{-- ═══════ EDIT MODAL ═══════ --}}
<template x-teleport="body">
    <div x-show="em.open" x-cloak
         class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center z-50 p-4"
         @keydown.escape.window="em.open = false" @click.self="em.open = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-semibold text-lg mb-4">Изменить бронирование</h3>

            <div x-show="em.error"
                 class="bg-red-50 border border-red-200 text-red-600 rounded-lg px-3 py-2 text-sm mb-4"
                 x-text="em.error"></div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Дата</label>
                        <input type="date" x-model="em.date"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Начало</label>
                        <input type="time" x-model="em.time"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Гостей</label>
                        <input type="number" x-model.number="em.guests" min="1" max="50"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Стол</label>
                        <select x-model="em.table_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Авто</option>
                            @foreach(collect($floors)->flatMap(fn($f) => $f['tables'] ?? []) as $table)
                                <option value="{{ $table['id'] }}">Стол {{ $table['number'] }} (до {{ $table['capacity'] }} чел.)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Комментарий</label>
                    <input type="text" x-model="em.comment" placeholder="Особые пожелания…"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button @click="editSave()" :disabled="em.saving"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition disabled:opacity-40">
                    <span x-show="!em.saving">Сохранить</span>
                    <span x-show="em.saving">Сохраняем…</span>
                </button>
                <button @click="em.open = false"
                        class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">
                    Отмена
                </button>
            </div>
        </div>
    </div>
</template>

<style>
    .btn-sm { @apply px-3 py-1.5 text-xs font-medium rounded-lg transition; }
</style>

<script>
function dashboard(apiBase, token, tz) {
    // Дата YYYY-MM-DD в timezone ресторана
    const todayInTz = () => new Date().toLocaleDateString('en-CA', { timeZone: tz });

    const apiFetch = (path, opts = {}) =>
        fetch(apiBase + path, {
            ...opts,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                ...(opts.headers || {}),
            },
            body: opts.body ? JSON.stringify(opts.body) : undefined,
        });

    return {
        apiBase, token, tz,
        date:     todayInTz(),
        todayStr: todayInTz(),
        bookings: [],
        loading:  false,
        error:    null,

        // Cancel modal
        cm: { open: false, booking: null, reason: '', loading: false },
        // Edit modal
        em: { open: false, booking: null, date: '', time: '', guests: 2,
              table_id: '', comment: '', saving: false, error: null },

        // ── Lifecycle ──────────────────────────────────────────────

        init() {
            this.reload();
        },

        // ── Load bookings ──────────────────────────────────────────

        async reload() {
            this.loading = true;
            this.error   = null;
            try {
                const r    = await apiFetch(`/bookings?date=${this.date}`);
                const json = await r.json();
                if (!r.ok) throw new Error(json.message || 'Ошибка загрузки');
                this.bookings = json.data || [];
            } catch (e) {
                this.error    = e.message;
                this.bookings = [];
            } finally {
                this.loading = false;
            }
        },

        // ── Date navigation ────────────────────────────────────────

        shiftDay(delta) {
            // Используем noon чтобы избежать DST-прыжков
            const d = new Date(this.date + 'T12:00:00');
            d.setDate(d.getDate() + delta);
            this.date = d.toLocaleDateString('en-CA', { timeZone: this.tz });
            this.reload();
        },
        goToday() {
            this.date = todayInTz();
            this.reload();
        },

        // ── Status actions ─────────────────────────────────────────

        async act(booking, action) {
            const r    = await apiFetch(`/bookings/${booking.id}/${action}`, { method: 'POST' });
            const json = await r.json();
            if (r.ok && json.data) this.replaceBooking(json.data);
        },

        cancelOpen(booking) {
            this.cm = { open: true, booking, reason: '', loading: false };
        },
        async cancelConfirm() {
            this.cm.loading = true;
            try {
                const r    = await apiFetch(`/bookings/${this.cm.booking.id}/cancel`, {
                    method: 'POST',
                    body:   { reason: this.cm.reason },
                });
                const json = await r.json();
                if (r.ok && json.data) this.replaceBooking(json.data);
                this.cm.open = false;
            } finally {
                this.cm.loading = false;
            }
        },

        // ── Edit ───────────────────────────────────────────────────

        editOpen(booking) {
            const s   = new Date(booking.booking_start);
            const pad = n => String(n).padStart(2, '0');
            this.em = {
                open:     true,
                booking,
                date:     s.toISOString().slice(0, 10),
                time:     pad(s.getHours()) + ':' + pad(s.getMinutes()),
                guests:   booking.guests_count,
                table_id: booking.table?.id || '',
                comment:  booking.comment || '',
                saving:   false,
                error:    null,
            };
        },
        async editSave() {
            this.em.saving = true;
            this.em.error  = null;
            try {
                const e  = this.em;
                const pad = n => String(n).padStart(2, '0');
                const start = new Date(`${e.date}T${e.time}:00`);
                const end   = new Date(start.getTime() + 120 * 60_000);
                const fmtDt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:00`;

                const body = {
                    guests_count:  e.guests,
                    booking_start: fmtDt(start),
                    booking_end:   fmtDt(end),
                    comment:       e.comment || null,
                };
                if (e.table_id) body.table_id = e.table_id;

                const r    = await apiFetch(`/bookings/${e.booking.id}`, { method: 'PATCH', body });
                const json = await r.json();

                if (!r.ok) { this.em.error = json.message || 'Ошибка сохранения'; return; }
                if (json.data) this.replaceBooking(json.data);
                this.em.open = false;
            } finally {
                this.em.saving = false;
            }
        },

        // ── Helpers ────────────────────────────────────────────────

        replaceBooking(updated) {
            const i = this.bookings.findIndex(b => b.id === updated.id);
            if (i !== -1) this.bookings[i] = updated;
        },

        count(status) {
            return this.bookings.filter(b => b.status.value === status).length;
        },

        hm(iso) {
            if (!iso) return '—';
            // Показываем время в timezone ресторана, а не браузера
            return new Date(iso).toLocaleTimeString('ru-RU', {
                timeZone: this.tz,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
        },

        humanDate(str) {
            // Noon чтобы парсинг не сдвинул дату при часовых поясах
            const d = new Date(str + 'T12:00:00');
            const days   = ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'];
            const months = ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
            return `${d.getDate()} ${months[d.getMonth()]}, ${days[d.getDay()]}`;
        },
    };
}
</script>
</body>
</html>
