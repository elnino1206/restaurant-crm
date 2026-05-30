<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Брони — {{ $restaurant['name'] ?? 'Dashboard' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .status-pending   { background:#FEF9EC; color:#B45309; border-color:#FDE68A; }
        .status-confirmed { background:#EFF6FF; color:#1D4ED8; border-color:#BFDBFE; }
        .status-completed { background:#F0FDF4; color:#15803D; border-color:#BBF7D0; }
        .status-cancelled { background:#FEF2F2; color:#DC2626; border-color:#FECACA; }
        .status-no_show   { background:#F3F4F6; color:#6B7280; border-color:#E5E7EB; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800" x-data="dashboard()" x-init="init()">

{{-- ══════════ HEADER ══════════ --}}
<header class="bg-white border-b border-gray-200 sticky top-0 z-20">
    <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="font-semibold text-base">{{ $restaurant['name'] ?? 'CRM' }}</span>
            <span class="text-gray-300">|</span>
            <a href="/admin/restaurants" class="text-sm text-gray-500 hover:text-gray-700">Настройки</a>
            <a href="/book/{{ \App\Domains\Restaurant\Models\Restaurant::where('name', $restaurant['name'] ?? '')->value('slug') ?? 'test-restaurant' }}" target="_blank"
               class="text-sm text-indigo-600 hover:text-indigo-800">Страница брони ↗</a>
        </div>
        <span class="text-sm text-gray-400">{{ now()->format('d.m.Y') }}</span>
    </div>
</header>

{{-- ══════════ DATE NAV ══════════ --}}
<div class="bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-4">
        <button @click="prevDay()" class="p-1.5 rounded hover:bg-gray-100 text-gray-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <div class="flex items-center gap-2">
            <input type="date" x-model="currentDate" @change="loadBookings()"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button @click="goToday()"
                    :class="currentDate === todayStr ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                Сегодня
            </button>
        </div>

        <button @click="nextDay()" class="p-1.5 rounded hover:bg-gray-100 text-gray-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>

        <span class="text-sm font-medium text-gray-700 ml-1" x-text="formatDate(currentDate)"></span>

        {{-- Stats --}}
        <div class="ml-auto flex items-center gap-3 text-sm">
            <span class="text-gray-500">Всего: <strong x-text="bookings.length"></strong></span>
            <span class="text-yellow-600">Ожидают: <strong x-text="countByStatus('pending')"></strong></span>
            <span class="text-blue-600">Подтв.: <strong x-text="countByStatus('confirmed')"></strong></span>
            <span class="text-green-600">Завершены: <strong x-text="countByStatus('completed')"></strong></span>
        </div>
    </div>
</div>

{{-- ══════════ MAIN ══════════ --}}
<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-16 text-gray-400">
        <svg class="animate-spin w-6 h-6 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        Загрузка…
    </div>

    {{-- Empty --}}
    <div x-show="!loading && bookings.length === 0" class="text-center py-16">
        <div class="text-4xl mb-4">📅</div>
        <p class="text-gray-500 text-lg">Бронирований на эту дату нет</p>
    </div>

    {{-- Bookings list --}}
    <div x-show="!loading && bookings.length > 0" class="space-y-3">
        <template x-for="b in bookings" :key="b.id">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-gray-300 transition">
                <div class="p-4 flex items-start gap-4">

                    {{-- Time column --}}
                    <div class="w-16 flex-shrink-0 text-center">
                        <div class="text-lg font-bold text-gray-900" x-text="formatTime(b.booking_start)"></div>
                        <div class="text-xs text-gray-400" x-text="formatTime(b.booking_end)"></div>
                    </div>

                    {{-- Table + guests --}}
                    <div class="w-20 flex-shrink-0 text-center">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-sm font-semibold mx-auto"
                             x-text="b.table ? '#' + b.table.number : '?'"></div>
                        <div class="text-xs text-gray-400 mt-1" x-text="b.guests_count + ' гост.'"></div>
                    </div>

                    {{-- Customer info --}}
                    <div class="flex-1 min-w-0">
                        <div class="font-medium truncate"
                             x-text="b.customer ? b.customer.name : 'Без имени'"></div>
                        <div class="text-sm text-gray-500" x-text="b.customer?.phone || '—'"></div>
                        <div class="text-xs text-gray-400 mt-0.5" x-show="b.comment" x-text="b.comment"></div>
                    </div>

                    {{-- Status badge --}}
                    <div class="flex-shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                              :class="'status-' + b.status.value"
                              x-text="b.status.label"></span>
                    </div>

                    {{-- Table capacity --}}
                    <div class="flex-shrink-0 text-right hidden sm:block">
                        <div class="text-xs text-gray-400" x-show="b.table">
                            вместимость <span x-text="b.table?.capacity"></span>
                        </div>
                        <div class="text-xs text-gray-300 mt-0.5" x-text="b.source?.label"></div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-1.5">
                        {{-- Confirm --}}
                        <button x-show="b.status.value === 'pending'"
                                @click="doAction(b, 'confirm')"
                                class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Подтвердить
                        </button>
                        {{-- Complete --}}
                        <button x-show="b.status.value === 'confirmed'"
                                @click="doAction(b, 'complete')"
                                class="px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Завершить
                        </button>
                        {{-- No-show --}}
                        <button x-show="b.status.value === 'confirmed'"
                                @click="doAction(b, 'no-show')"
                                class="px-3 py-1.5 text-xs font-medium bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                            Не явился
                        </button>
                        {{-- Cancel --}}
                        <button x-show="['pending','confirmed'].includes(b.status.value)"
                                @click="openCancel(b)"
                                class="px-3 py-1.5 text-xs font-medium bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition">
                            Отмена
                        </button>
                        {{-- Edit --}}
                        <button x-show="['pending','confirmed'].includes(b.status.value)"
                                @click="openEdit(b)"
                                class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                            ✏️
                        </button>
                    </div>

                </div>
            </div>
        </template>
    </div>

</div>

{{-- ══════════ CANCEL MODAL ══════════ --}}
<div x-show="cancelModal.open" x-cloak
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4"
     @keydown.escape.window="cancelModal.open = false">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="font-semibold text-lg mb-2">Отменить бронирование</h3>
        <p class="text-sm text-gray-500 mb-4"
           x-text="cancelModal.booking ? formatTime(cancelModal.booking.booking_start) + ' · ' + (cancelModal.booking.customer?.name || '—') : ''"></p>
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1.5">Причина (необязательно)</label>
            <input type="text" x-model="cancelModal.reason" placeholder="Клиент сам отменил…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
        </div>
        <div class="flex gap-2">
            <button @click="confirmCancel()"
                    class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                Отменить бронь
            </button>
            <button @click="cancelModal.open = false"
                    class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">
                Назад
            </button>
        </div>
    </div>
</div>

{{-- ══════════ EDIT MODAL ══════════ --}}
<div x-show="editModal.open" x-cloak
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4"
     @keydown.escape.window="editModal.open = false">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="font-semibold text-lg mb-4">Изменить бронирование</h3>

        <div class="error-msg bg-red-50 border border-red-200 text-red-600 rounded-lg px-3 py-2 text-sm mb-4"
             x-show="editModal.error" x-text="editModal.error"></div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Дата</label>
                    <input type="date" x-model="editModal.date"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Время</label>
                    <input type="time" x-model="editModal.time"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Гостей</label>
                    <input type="number" x-model="editModal.guests" min="1" max="50"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Стол</label>
                    <select x-model="editModal.table_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Авто</option>
                        @foreach(collect($floors)->flatMap(fn($f) => $f['tables'] ?? []) as $table)
                            <option value="{{ $table['id'] }}">Стол {{ $table['number'] }} ({{ $table['capacity'] }} чел.)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Комментарий</label>
                <input type="text" x-model="editModal.comment" placeholder="Особые пожелания…"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex gap-2 mt-5">
            <button @click="saveEdit()" :disabled="editModal.saving"
                    class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-40">
                <span x-show="!editModal.saving">Сохранить</span>
                <span x-show="editModal.saving">Сохраняем…</span>
            </button>
            <button @click="editModal.open = false"
                    class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">
                Отмена
            </button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

function dashboard() {
    const today = new Date().toISOString().slice(0, 10);

    return {
        currentDate: '{{ $date }}',
        todayStr:    today,
        bookings:    @json($bookings),
        loading:     false,

        cancelModal: { open: false, booking: null, reason: '' },
        editModal:   { open: false, booking: null, date: '', time: '', guests: 2,
                       table_id: '', comment: '', saving: false, error: null },

        init() {},

        // ── Date navigation ─────────────────────────────────

        prevDay() {
            const d = new Date(this.currentDate + 'T00:00:00');
            d.setDate(d.getDate() - 1);
            this.currentDate = d.toISOString().slice(0, 10);
            this.loadBookings();
        },
        nextDay() {
            const d = new Date(this.currentDate + 'T00:00:00');
            d.setDate(d.getDate() + 1);
            this.currentDate = d.toISOString().slice(0, 10);
            this.loadBookings();
        },
        goToday() {
            this.currentDate = this.todayStr;
            this.loadBookings();
        },

        async loadBookings() {
            this.loading = true;
            try {
                const r = await fetch('/dashboard/bookings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ date: this.currentDate }),
                });
                const data = await r.json();
                this.bookings = data.bookings || [];
            } finally {
                this.loading = false;
            }
        },

        // ── Actions ─────────────────────────────────────────

        async doAction(booking, action) {
            const r = await fetch(`/dashboard/bookings/${booking.id}/${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({}),
            });
            const data = await r.json();
            if (r.ok && data.data) {
                const idx = this.bookings.findIndex(b => b.id === booking.id);
                if (idx !== -1) this.bookings[idx] = data.data;
            }
        },

        openCancel(booking) {
            this.cancelModal = { open: true, booking, reason: '' };
        },
        async confirmCancel() {
            const b = this.cancelModal.booking;
            const r = await fetch(`/dashboard/bookings/${b.id}/cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ reason: this.cancelModal.reason }),
            });
            const data = await r.json();
            if (r.ok && data.data) {
                const idx = this.bookings.findIndex(x => x.id === b.id);
                if (idx !== -1) this.bookings[idx] = data.data;
            }
            this.cancelModal.open = false;
        },

        openEdit(booking) {
            const start = new Date(booking.booking_start);
            const pad = n => String(n).padStart(2, '0');
            this.editModal = {
                open:     true,
                booking,
                date:     start.toISOString().slice(0, 10),
                time:     pad(start.getHours()) + ':' + pad(start.getMinutes()),
                guests:   booking.guests_count,
                table_id: booking.table?.id || '',
                comment:  booking.comment || '',
                saving:   false,
                error:    null,
            };
        },
        async saveEdit() {
            this.editModal.saving = true;
            this.editModal.error  = null;
            try {
                const b    = this.editModal;
                const startDt = b.date + 'T' + b.time + ':00';
                const endDate = new Date(new Date(startDt).getTime() + 120 * 60000);
                const pad = n => String(n).padStart(2, '0');
                const endDt = endDate.getFullYear() + '-' +
                    pad(endDate.getMonth() + 1) + '-' +
                    pad(endDate.getDate()) + 'T' +
                    pad(endDate.getHours()) + ':' +
                    pad(endDate.getMinutes()) + ':00';

                const payload = {
                    guests_count:  parseInt(b.guests),
                    booking_start: startDt,
                    booking_end:   endDt,
                    comment:       b.comment || null,
                };
                if (b.table_id) payload.table_id = b.table_id;

                const r = await fetch(`/dashboard/bookings/${b.booking.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await r.json();
                if (!r.ok) {
                    this.editModal.error = data.message || 'Ошибка сохранения.';
                    return;
                }
                if (data.data) {
                    const idx = this.bookings.findIndex(x => x.id === b.booking.id);
                    if (idx !== -1) this.bookings[idx] = data.data;
                }
                this.editModal.open = false;
            } finally {
                this.editModal.saving = false;
            }
        },

        // ── Helpers ─────────────────────────────────────────

        countByStatus(status) {
            return this.bookings.filter(b => b.status.value === status).length;
        },

        formatTime(iso) {
            if (!iso) return '—';
            const d = new Date(iso);
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            const days   = ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'];
            const months = ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
            return d.getDate() + ' ' + months[d.getMonth()] + ', ' + days[d.getDay()];
        },
    };
}
</script>
</body>
</html>
