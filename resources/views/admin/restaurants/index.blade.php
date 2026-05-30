<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рестораны — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Рестораны</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление ресторанами</p>
        </div>
        <a href="{{ route('admin.restaurants.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + Добавить ресторан
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        @if(count($restaurants))
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3">Название</th>
                    <th class="px-5 py-3">Slug</th>
                    <th class="px-5 py-3">Timezone</th>
                    <th class="px-5 py-3">Телефон</th>
                    <th class="px-5 py-3">Статус</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($restaurants as $r)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium">{{ $r['name'] }}</td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $r['slug'] }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $r['timezone'] }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $r['phone'] ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $r['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $r['is_active'] ? 'Активен' : 'Неактивен' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.restaurants.edit', $r['id']) }}"
                           class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                            Настроить →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p-12 text-center text-gray-400">
            <p class="text-lg">Рестораны не найдены</p>
            <a href="{{ route('admin.restaurants.create') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">
                Создать первый ресторан
            </a>
        </div>
        @endif
    </div>

</div>
</body>
</html>
