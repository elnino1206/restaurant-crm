<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant CRM — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-7xl mx-auto px-4 py-8 space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Restaurant CRM</h1>
        <span class="text-sm text-gray-500">Данные получены через API</span>
    </div>

    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ $error }}
        </div>
    @endif

    {{-- Restaurant --}}
    @if($restaurant)
    <section>
        <h2 class="text-xl font-semibold mb-3">Ресторан</h2>
        <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Название</p>
                <p class="font-medium mt-1">{{ $restaurant['name'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Slug</p>
                <p class="font-medium mt-1">{{ $restaurant['slug'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Timezone</p>
                <p class="font-medium mt-1">{{ $restaurant['timezone'] }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Телефон</p>
                <p class="font-medium mt-1">{{ $restaurant['phone'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Адрес</p>
                <p class="font-medium mt-1">{{ $restaurant['address'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Статус</p>
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-medium {{ $restaurant['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $restaurant['is_active'] ? 'Активен' : 'Неактивен' }}
                </span>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-gray-500 uppercase tracking-wide">ID</p>
                <p class="font-mono text-xs mt-1 text-gray-400">{{ $restaurant['id'] }}</p>
            </div>
        </div>
    </section>
    @endif

    {{-- Floors & Tables --}}
    @if(count($floors))
    <section>
        <h2 class="text-xl font-semibold mb-3">Залы и столы</h2>
        <div class="space-y-4">
            @foreach($floors as $floor)
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-lg mb-4">{{ $floor['name'] }}
                    <span class="text-sm text-gray-400 font-normal ml-2">{{ count($floor['tables'] ?? []) }} стол(ов)</span>
                </h3>
                @if(count($floor['tables'] ?? []))
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-gray-500">
                                <th class="pb-2 pr-6">Номер</th>
                                <th class="pb-2 pr-6">Вместимость</th>
                                <th class="pb-2 pr-6">Мин. гостей</th>
                                <th class="pb-2">Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($floor['tables'] as $table)
                            <tr class="border-b last:border-0">
                                <td class="py-2 pr-6 font-medium">Стол {{ $table['number'] }}</td>
                                <td class="py-2 pr-6">{{ $table['capacity'] }} чел.</td>
                                <td class="py-2 pr-6">{{ $table['min_capacity'] }} чел.</td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $table['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $table['is_active'] ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Bookings --}}
    <section>
        <h2 class="text-xl font-semibold mb-3">
            Бронирования
            <span class="text-base text-gray-400 font-normal ml-2">{{ count($bookings) }} записей</span>
        </h2>

        @php
        $statusColors = [
            'pending'   => 'bg-yellow-100 text-yellow-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            'no_show'   => 'bg-gray-100 text-gray-600',
        ];
        @endphp

        @if(count($bookings))
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3">Гостей</th>
                        <th class="px-4 py-3">Начало</th>
                        <th class="px-4 py-3">Конец</th>
                        <th class="px-4 py-3">Стол</th>
                        <th class="px-4 py-3">Клиент</th>
                        <th class="px-4 py-3">Источник</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$booking['status']['value']] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $booking['status']['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $booking['guests_count'] }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ \Carbon\Carbon::parse($booking['booking_start'])->setTimezone('Europe/Moscow')->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ \Carbon\Carbon::parse($booking['booking_end'])->setTimezone('Europe/Moscow')->format('H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            {{ isset($booking['table']) ? 'Стол '.$booking['table']['number'] : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $booking['customer']['name'] ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $booking['source']['label'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
            Бронирований нет
        </div>
        @endif
    </section>

    {{-- Customers --}}
    <section>
        <h2 class="text-xl font-semibold mb-3">
            Клиенты
            <span class="text-base text-gray-400 font-normal ml-2">{{ count($customers) }} записей</span>
        </h2>

        @if(count($customers))
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
                        <th class="px-4 py-3">Имя</th>
                        <th class="px-4 py-3">Телефон</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Telegram</th>
                        <th class="px-4 py-3">Заметки</th>
                        <th class="px-4 py-3">Добавлен</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $customer['name'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $customer['phone'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $customer['email'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            @if($customer['telegram_username'])
                                @{{ $customer['telegram_username'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $customer['notes'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ \Carbon\Carbon::parse($customer['created_at'])->format('d.m.Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
            Клиентов нет
        </div>
        @endif
    </section>

    {{-- Time Slot Configs --}}
    @if(count($timeSlotConfig))
    <section>
        <h2 class="text-xl font-semibold mb-3">Конфигурация слотов</h2>
        @php
        $days = ['', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        @endphp
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
                        <th class="px-4 py-3">День</th>
                        <th class="px-4 py-3">Открытие</th>
                        <th class="px-4 py-3">Закрытие</th>
                        <th class="px-4 py-3">Слот (мин)</th>
                        <th class="px-4 py-3">Бронь (мин)</th>
                        <th class="px-4 py-3">Выходной</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlotConfig as $config)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">{{ $days[$config['day_of_week']] }}</td>
                        <td class="px-4 py-3">{{ $config['open_time'] }}</td>
                        <td class="px-4 py-3">{{ $config['close_time'] }}</td>
                        <td class="px-4 py-3">{{ $config['slot_duration'] }}</td>
                        <td class="px-4 py-3">{{ $config['booking_duration'] }}</td>
                        <td class="px-4 py-3">
                            @if($config['is_day_off'])
                                <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-600">Да</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-600">Нет</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    <footer class="text-center text-xs text-gray-400 py-4">
        Restaurant CRM · Данные через <code class="bg-gray-100 px-1 rounded">GET /api/v1/*</code>
    </footer>

</div>
</body>
</html>
