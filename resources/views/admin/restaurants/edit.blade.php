<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $restaurant['name'] ?? 'Ресторан' }} — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.restaurants.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Все рестораны</a>
            <h1 class="text-2xl font-bold mt-1">{{ $restaurant['name'] }}</h1>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $restaurant['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $restaurant['is_active'] ? 'Активен' : 'Неактивен' }}
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm space-y-1">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    {{-- ═══════════════════════════════════════
         СЕКЦИЯ 1: Настройки ресторана
    ════════════════════════════════════════ --}}
    <section>
        <h2 class="text-lg font-semibold mb-3">Настройки ресторана</h2>
        <div class="bg-white rounded-xl shadow p-6">
            <form method="POST" action="{{ route('admin.restaurants.update', $restaurant['id']) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                        <input type="text" name="name" value="{{ $restaurant['name'] }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select name="timezone"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(['Europe/Moscow','Europe/Kiev','Asia/Yekaterinburg','Asia/Novosibirsk','Asia/Krasnoyarsk','Asia/Irkutsk','Asia/Vladivostok','UTC'] as $tz)
                                <option value="{{ $tz }}" {{ $restaurant['timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                        <input type="text" name="phone" value="{{ $restaurant['phone'] }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                        <select name="is_active"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="1" {{ $restaurant['is_active'] ? 'selected' : '' }}>Активен</option>
                            <option value="0" {{ !$restaurant['is_active'] ? 'selected' : '' }}>Неактивен</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Адрес</label>
                    <input type="text" name="address" value="{{ $restaurant['address'] }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         СЕКЦИЯ 2: Залы и столы
    ════════════════════════════════════════ --}}
    <section x-data="{ addFloorOpen: false }">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Залы и столы</h2>
            <button @click="addFloorOpen = true"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                + Добавить зал
            </button>
        </div>

        {{-- Модал: Добавить зал --}}
        <div x-show="addFloorOpen" x-cloak
             class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
             @keydown.escape.window="addFloorOpen = false">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
                <h3 class="font-semibold text-lg mb-4">Новый зал</h3>
                <form method="POST" action="{{ route('admin.restaurants.floors.store', $restaurant['id']) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Название зала *</label>
                            <input type="text" name="name" required autofocus
                                   placeholder="Основной зал"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Порядок сортировки</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-5">
                        <button type="submit"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                            Создать
                        </button>
                        <button type="button" @click="addFloorOpen = false"
                                class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:text-gray-800">
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Список залов --}}
        @if(count($floors))
            @foreach($floors as $floor)
            <div x-data="{
                    open: true,
                    editFloorOpen: false,
                    addTableOpen: false,
                    editTable: null,
                    floorName: '{{ addslashes($floor['name']) }}',
                    floorSort: {{ $floor['sort_order'] }}
                 }"
                 class="bg-white rounded-xl shadow mb-4 overflow-hidden">

                {{-- Floor header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b bg-gray-50">
                    <button @click="open = !open" class="flex items-center gap-2 font-semibold text-left flex-1">
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        {{ $floor['name'] }}
                        <span class="text-sm font-normal text-gray-400 ml-1">{{ count($floor['tables'] ?? []) }} стол(ов)</span>
                    </button>
                    <div class="flex items-center gap-2 ml-4">
                        <button @click="editFloorOpen = true"
                                class="text-sm text-gray-500 hover:text-indigo-600 px-2 py-1 rounded hover:bg-indigo-50 transition">
                            Изменить
                        </button>
                        <form method="POST" action="{{ route('admin.restaurants.floors.destroy', [$restaurant['id'], $floor['id']]) }}"
                              onsubmit="return confirm('Удалить зал «{{ $floor['name'] }}» и все столы?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition">
                                Удалить
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Модал: Редактировать зал --}}
                <div x-show="editFloorOpen" x-cloak
                     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
                     @keydown.escape.window="editFloorOpen = false">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
                        <h3 class="font-semibold text-lg mb-4">Редактировать зал</h3>
                        <form method="POST" action="{{ route('admin.restaurants.floors.update', [$restaurant['id'], $floor['id']]) }}">
                            @csrf @method('PUT')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Название зала</label>
                                    <input type="text" name="name" x-model="floorName" required
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Порядок</label>
                                    <input type="number" name="sort_order" x-model="floorSort" min="0"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="flex gap-3 mt-5">
                                <button type="submit"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                    Сохранить
                                </button>
                                <button type="button" @click="editFloorOpen = false"
                                        class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:text-gray-800">
                                    Отмена
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tables --}}
                <div x-show="open">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 uppercase tracking-wide border-b">
                                <th class="px-5 py-2">Стол №</th>
                                <th class="px-5 py-2">Вместимость</th>
                                <th class="px-5 py-2">Мин. гостей</th>
                                <th class="px-5 py-2">Статус</th>
                                <th class="px-5 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($floor['tables'] ?? [] as $table)
                            <tr class="border-b last:border-0 hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium">{{ $table['number'] }}</td>
                                <td class="px-5 py-3">{{ $table['capacity'] }} чел.</td>
                                <td class="px-5 py-3 text-gray-500">{{ $table['min_capacity'] }} чел.</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ $table['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $table['is_active'] ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right flex gap-2 justify-end">
                                    <button @click="editTable = {{ json_encode($table) }}"
                                            class="text-sm text-gray-500 hover:text-indigo-600 px-2 py-1 rounded hover:bg-indigo-50 transition">
                                        Изменить
                                    </button>
                                    <form method="POST" action="{{ route('admin.restaurants.tables.destroy', [$restaurant['id'], $table['id']]) }}"
                                          onsubmit="return confirm('Удалить стол {{ $table['number'] }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition">
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-4 text-center text-gray-400 text-sm">Столов нет</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-5 py-3 border-t bg-gray-50">
                        <button @click="addTableOpen = true"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            + Добавить стол
                        </button>
                    </div>
                </div>

                {{-- Модал: Добавить стол --}}
                <div x-show="addTableOpen" x-cloak
                     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
                     @keydown.escape.window="addTableOpen = false">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
                        <h3 class="font-semibold text-lg mb-4">Новый стол в зале «{{ $floor['name'] }}»</h3>
                        <form method="POST" action="{{ route('admin.restaurants.tables.store', [$restaurant['id'], $floor['id']]) }}">
                            @csrf
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер стола *</label>
                                        <input type="text" name="number" required placeholder="1"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Вместимость *</label>
                                        <input type="number" name="capacity" required min="1" placeholder="4"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Мин. гостей</label>
                                        <input type="number" name="min_capacity" value="1" min="1"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div class="flex items-end pb-1">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" checked
                                                   class="rounded border-gray-300 text-indigo-600">
                                            <span class="text-sm text-gray-700">Активен</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-5">
                                <button type="submit"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                    Добавить
                                </button>
                                <button type="button" @click="addTableOpen = false"
                                        class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:text-gray-800">
                                    Отмена
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Модал: Редактировать стол --}}
                <div x-show="editTable !== null" x-cloak
                     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
                     @keydown.escape.window="editTable = null">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop x-show="editTable !== null">
                        <h3 class="font-semibold text-lg mb-4">Редактировать стол</h3>
                        <template x-if="editTable">
                            <form method="POST" :action="`{{ url('admin/restaurants/'.$restaurant['id'].'/tables') }}/${editTable.id}`">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Номер стола</label>
                                            <input type="text" name="number" :value="editTable.number" required
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Вместимость</label>
                                            <input type="number" name="capacity" :value="editTable.capacity" required min="1"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Мин. гостей</label>
                                            <input type="number" name="min_capacity" :value="editTable.min_capacity" min="1"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div class="flex items-end pb-1">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="is_active" value="1" :checked="editTable.is_active"
                                                       class="rounded border-gray-300 text-indigo-600">
                                                <span class="text-sm text-gray-700">Активен</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-3 mt-5">
                                    <button type="submit"
                                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                        Сохранить
                                    </button>
                                    <button type="button" @click="editTable = null"
                                            class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:text-gray-800">
                                        Отмена
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>

            </div>
            @endforeach
        @else
            <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
                <p>Залов пока нет. Добавьте первый зал.</p>
            </div>
        @endif
    </section>

</div>
</body>
</html>
