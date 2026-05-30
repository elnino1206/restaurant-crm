<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование · {{ $restaurant->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* ─── Design tokens (из шаблонов 390/834/1440) ─── */
        :root {
            --bg:         #F5F5F6;
            --white:      #FFFFFF;
            --text:       #08080A;
            --text-2:     #6B6B70;
            --text-3:     #9B9BA0;
            --border:     #D6D6D8;
            --border-2:   #EBEBEC;
            --accent:     #08080A;
            --accent-fg:  #FFFFFF;
            --error:      #D94040;
            --success:    #1A8A45;
            --slot-free:  #FFFFFF;
            --slot-last:  #FFF8EC;
            --slot-last-border: #E8A020;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Archivo', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Layout ─── */
        .page { display: flex; flex-direction: column; min-height: 100vh; }

        .header {
            background: var(--white);
            border-bottom: 1px solid var(--border-2);
            padding: 0 16px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .header-name {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .header-phone {
            font-size: 13px;
            color: var(--text-2);
            text-decoration: none;
        }
        .header-phone:hover { color: var(--text); }

        .status-bar {
            background: var(--white);
            border-bottom: 1px solid var(--border-2);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-2);
        }
        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--success);
            flex-shrink: 0;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .main { flex: 1; padding: 20px 16px 100px; }

        .widget {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-2);
            overflow: hidden;
        }
        .section {
            padding: 20px 16px;
            border-bottom: 1px solid var(--border-2);
        }
        .section:last-child { border-bottom: none; }
        .section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 14px;
        }

        /* ─── Guests counter ─── */
        .counter {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .counter-btn {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            background: transparent;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--text);
            transition: border-color .15s, background .15s;
        }
        .counter-btn:hover:not(:disabled) { border-color: var(--text); }
        .counter-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .counter-value {
            font-size: 22px;
            font-weight: 600;
            min-width: 32px;
            text-align: center;
        }
        .counter-caption { font-size: 13px; color: var(--text-2); margin-left: 4px; }

        /* ─── Date picker ─── */
        .date-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .date-tab {
            padding: 7px 16px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            background: transparent;
            font-size: 13px;
            font-family: inherit;
            cursor: pointer;
            color: var(--text);
            transition: all .15s;
            white-space: nowrap;
        }
        .date-tab.active, .date-tab:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-fg);
        }

        .week-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }
        .week-day {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 4px;
            border-radius: 10px;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all .15s;
        }
        .week-day:hover { border-color: var(--border); }
        .week-day.active {
            background: var(--accent);
            border-color: var(--accent);
        }
        .week-day.active .wd-name,
        .week-day.active .wd-num { color: var(--accent-fg); }
        .week-day.past { opacity: 0.35; cursor: not-allowed; pointer-events: none; }
        .wd-name { font-size: 10px; color: var(--text-3); margin-bottom: 4px; }
        .wd-num  { font-size: 14px; font-weight: 500; color: var(--text); }

        /* ─── Time period tabs ─── */
        .period-tabs {
            display: flex;
            gap: 8px;
        }
        .period-tab {
            flex: 1;
            padding: 9px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: transparent;
            font-size: 13px;
            font-family: inherit;
            cursor: pointer;
            color: var(--text);
            text-align: center;
            transition: all .15s;
        }
        .period-tab.active {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-fg);
            font-weight: 500;
        }

        /* ─── Time slots ─── */
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .slot-btn {
            padding: 11px 4px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--slot-free);
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            color: var(--text);
            text-align: center;
            transition: all .15s;
            position: relative;
        }
        .slot-btn:hover { border-color: var(--accent); }
        .slot-btn.active {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-fg);
        }
        .slot-btn.last {
            background: var(--slot-last);
            border-color: var(--slot-last-border);
        }
        .slot-badge {
            display: block;
            font-size: 9px;
            font-weight: 400;
            color: var(--slot-last-border);
            margin-top: 2px;
        }
        .slot-btn.active .slot-badge { color: rgba(255,255,255,.7); }

        .slots-empty {
            text-align: center;
            padding: 28px 0;
            color: var(--text-3);
            font-size: 14px;
        }

        /* ─── Sticky bottom bar ─── */
        .bottom-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--white);
            border-top: 1px solid var(--border-2);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            z-index: 20;
        }
        .bottom-summary {
            font-size: 13px;
            color: var(--text-2);
            line-height: 1.4;
            flex: 1;
            overflow: hidden;
        }
        .bottom-summary strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-primary {
            background: var(--accent);
            color: var(--accent-fg);
            border: none;
            border-radius: 12px;
            padding: 13px 24px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            white-space: nowrap;
            transition: opacity .15s;
            flex-shrink: 0;
        }
        .btn-primary:hover { opacity: 0.85; }
        .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ─── Modal overlay ─── */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            display: flex;
            align-items: flex-end;
            z-index: 50;
        }
        .modal {
            background: var(--white);
            border-radius: 20px 20px 0 0;
            width: 100%;
            padding: 28px 20px 32px;
        }
        .modal-handle {
            width: 36px; height: 4px;
            border-radius: 2px;
            background: var(--border);
            margin: 0 auto 24px;
        }
        .modal-title {
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .modal-subtitle {
            font-size: 13px;
            color: var(--text-2);
            margin-bottom: 24px;
        }

        /* ─── Form fields ─── */
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 6px;
            letter-spacing: 0.03em;
        }
        .field input, .field textarea {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            background: var(--white);
            outline: none;
            transition: border-color .15s;
        }
        .field input:focus, .field textarea:focus { border-color: var(--accent); }
        .field textarea { resize: none; height: 76px; }

        /* ─── Error / Confirm states ─── */
        .error-msg {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--error);
            margin-bottom: 16px;
        }

        /* Confirm screen */
        .confirm-screen {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            text-align: center;
        }
        .confirm-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: #ECFDF5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
        }
        .confirm-title { font-size: 22px; font-weight: 600; margin-bottom: 8px; }
        .confirm-subtitle { font-size: 14px; color: var(--text-2); margin-bottom: 28px; line-height: 1.6; }
        .confirm-card {
            background: var(--white);
            border: 1px solid var(--border-2);
            border-radius: 16px;
            padding: 20px;
            width: 100%;
            max-width: 360px;
            text-align: left;
            margin-bottom: 24px;
        }
        .confirm-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-2);
            font-size: 14px;
        }
        .confirm-row:last-child { border-bottom: none; }
        .confirm-row-label { color: var(--text-2); }
        .confirm-row-value { font-weight: 500; }

        .btn-secondary {
            background: transparent;
            color: var(--text);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 13px 32px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: all .15s;
        }
        .btn-secondary:hover { border-color: var(--text); }

        /* ─── Tablet 834px ─── */
        @media (min-width: 640px) {
            .header { padding: 0 32px; height: 64px; }
            .header-name { font-size: 16px; }
            .status-bar { padding: 12px 32px; }
            .main { padding: 32px 32px 100px; }
            .widget { max-width: 540px; margin: 0 auto; }
            .slots-grid { grid-template-columns: repeat(5, 1fr); }
            .section { padding: 24px 24px; }
            .bottom-bar { padding: 16px 32px; }
            .modal { max-width: 480px; margin: 0 auto; border-radius: 20px; }
            .overlay { align-items: center; }
        }

        /* Hide side-info on mobile/tablet */
        .side-info { display: none; }

        /* ─── Desktop 1440px ─── */
        @media (min-width: 1024px) {
            .header { padding: 0 60px; }
            .status-bar { padding: 12px 60px; }
            .main {
                padding: 48px 60px 100px;
                display: grid;
                grid-template-columns: 480px 1fr;
                gap: 32px;
                align-items: start;
            }
            .main-widget { position: sticky; top: 90px; }
            .widget { max-width: none; margin: 0; }
            .slots-grid { grid-template-columns: repeat(4, 1fr); }
            .side-info {
                display: block;
                background: var(--white);
                border-radius: 16px;
                border: 1px solid var(--border-2);
                padding: 28px;
            }
            .side-title { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
            .side-address { font-size: 14px; color: var(--text-2); margin-bottom: 20px; line-height: 1.5; }
            .side-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #F0FDF4;
                border: 1px solid #BBF7D0;
                border-radius: 20px;
                padding: 6px 12px;
                font-size: 12px;
                color: var(--success);
                font-weight: 500;
            }
            .bottom-bar { padding: 16px 60px; }
            .modal { max-width: 520px; border-radius: 20px; }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
<div class="page" x-data="booking('{{ $restaurant->slug }}', '{{ $restaurant->timezone }}', '{{ $restaurant->name }}', '{{ $restaurant->address ?? '' }}')" x-init="init()">

    {{-- ══════════════════════ CONFIRM SCREEN ══════════════════════ --}}
    <template x-if="step === 'confirmed'">
        <div class="confirm-screen">
            <div class="confirm-icon">✓</div>
            <h1 class="confirm-title">Бронь подтверждена!</h1>
            <p class="confirm-subtitle" x-text="`Ждём вас в ресторане «${confirmation.restaurant_name}»`"></p>

            <div class="confirm-card">
                <div class="confirm-row">
                    <span class="confirm-row-label">Дата и время</span>
                    <span class="confirm-row-value" x-text="confirmation.booking_start"></span>
                </div>
                <div class="confirm-row">
                    <span class="confirm-row-label">Гостей</span>
                    <span class="confirm-row-value" x-text="guests + ' ' + guestsWord(guests)"></span>
                </div>
                <div class="confirm-row">
                    <span class="confirm-row-label">Имя</span>
                    <span class="confirm-row-value" x-text="name"></span>
                </div>
                <div class="confirm-row">
                    <span class="confirm-row-label">Телефон</span>
                    <span class="confirm-row-value" x-text="phone"></span>
                </div>
                <div class="confirm-row">
                    <span class="confirm-row-label">Номер брони</span>
                    <span class="confirm-row-value" style="font-size:12px; font-family: monospace;" x-text="confirmation.id.substring(0,8)"></span>
                </div>
            </div>

            <p style="font-size: 13px; color: var(--text-2); margin-bottom: 20px;">
                Предоплата не требуется. Бронь действует 15 минут.
            </p>

            <button class="btn-secondary" @click="resetBooking()">Забронировать ещё раз</button>
        </div>
    </template>

    {{-- ══════════════════════ MAIN PAGE ══════════════════════ --}}
    <template x-if="step !== 'confirmed'">
        <div>
            {{-- Header --}}
            <header class="header">
                <span class="header-name">{{ $restaurant->name }}</span>
                @if($restaurant->phone)
                    <a href="tel:{{ $restaurant->phone }}" class="header-phone">{{ $restaurant->phone }}</a>
                @endif
            </header>

            {{-- Status bar --}}
            <div class="status-bar">
                <span class="status-dot"></span>
                <span>доступность в реальном времени · обновлено только что</span>
            </div>

            {{-- Main content --}}
            <main class="main">
                <div class="main-widget">
                    <div class="widget">

                        {{-- Guests --}}
                        <div class="section">
                            <p class="section-label">Гости</p>
                            <div class="counter">
                                <button class="counter-btn" @click="guests--" :disabled="guests <= 1">−</button>
                                <span class="counter-value" x-text="guests"></span>
                                <button class="counter-btn" @click="guests++" :disabled="guests >= 20">+</button>
                                <span class="counter-caption" x-text="guestsWord(guests)"></span>
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="section">
                            <p class="section-label">Дата</p>
                            <div class="date-tabs">
                                <button class="date-tab" :class="{ active: selectedDate === todayStr }" @click="setDate(todayStr)">Сегодня</button>
                                <button class="date-tab" :class="{ active: selectedDate === tomorrowStr }" @click="setDate(tomorrowStr)">Завтра</button>
                            </div>
                            <div class="week-grid">
                                <template x-for="day in weekDays" :key="day.date">
                                    <button class="week-day"
                                        :class="{ active: selectedDate === day.date, past: day.past }"
                                        @click="!day.past && setDate(day.date)">
                                        <span class="wd-name" x-text="day.name"></span>
                                        <span class="wd-num" x-text="day.num"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Time period --}}
                        <div class="section">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                                <p class="section-label" style="margin-bottom:0;">Время</p>
                                {{-- Подсказка о прошедших слотах --}}
                                <span x-show="slotsHint"
                                      x-text="slotsHint"
                                      style="font-size:11px; color:var(--text-3); text-align:right; max-width:180px; line-height:1.3;"></span>
                            </div>

                            <div class="period-tabs" style="margin-bottom: 16px;">
                                <button class="period-tab" :class="{ active: period === 'all' }" @click="period = 'all'">Все</button>
                                <button class="period-tab"
                                    :class="{ active: period === 'day' }"
                                    @click="period = 'day'">
                                    День
                                    <span x-show="period !== 'day' && allSlots.filter(s => parseInt(s) < 17).length === 0 && allSlots.length > 0"
                                          style="font-size:9px; opacity:.5; margin-left:2px;">·</span>
                                </button>
                                <button class="period-tab" :class="{ active: period === 'evening' }" @click="period = 'evening'">Вечер</button>
                            </div>

                            {{-- Slots --}}
                            <div x-show="loadingSlots" style="text-align:center; padding: 24px 0; color: var(--text-3); font-size: 14px;">
                                Загружаем слоты…
                            </div>

                            <template x-if="!loadingSlots">
                                <div>
                                    <template x-if="filteredSlots.length === 0">
                                        <div class="slots-empty">
                                            <template x-if="allSlots.length === 0">
                                                <div>
                                                    <div style="font-size:24px; margin-bottom:8px;">😔</div>
                                                    <div>На выбранную дату мест нет</div>
                                                    <div style="font-size:12px; margin-top:4px; color:var(--text-3);">Попробуйте другую дату</div>
                                                </div>
                                            </template>
                                            <template x-if="allSlots.length > 0 && period === 'day'">
                                                <div>
                                                    <div style="font-size:24px; margin-bottom:8px;">🌆</div>
                                                    <div>Дневные слоты закончились</div>
                                                    <div style="font-size:12px; margin-top:6px; color:var(--text-3);">
                                                        Доступно вечернее время —
                                                        <button @click="period = 'evening'"
                                                                style="background:none;border:none;color:var(--accent);font-size:12px;cursor:pointer;text-decoration:underline;padding:0;">
                                                            показать вечер
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="allSlots.length > 0 && period !== 'day'">
                                                <div>Нет слотов для выбранного периода</div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="filteredSlots.length > 0">
                                        <div class="slots-grid">
                                            <template x-for="(slot, i) in filteredSlots" :key="slot">
                                                <button class="slot-btn"
                                                    :class="{
                                                        active: selectedSlot === slot,
                                                        last: i === filteredSlots.length - 1 && filteredSlots.length <= 3
                                                    }"
                                                    @click="selectedSlot = slot">
                                                    <span x-text="slot"></span>
                                                    <span class="slot-badge"
                                                        x-show="i === filteredSlots.length - 1 && filteredSlots.length <= 3">
                                                        последний
                                                    </span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>

                {{-- Desktop side info (hidden on mobile/tablet via CSS) --}}
                <div class="side-info">
                    <h2 class="side-title">{{ $restaurant->name }}</h2>
                    @if($restaurant->address)
                        <p class="side-address">{{ $restaurant->address }}</p>
                    @endif
                    <div class="side-badge">
                        <span style="width:6px;height:6px;border-radius:50%;background:var(--success);display:inline-block;"></span>
                        Бронь без предоплаты
                    </div>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-2);">
                        <p style="font-size: 12px; color: var(--text-3); margin-bottom: 8px;">КАК ЭТО РАБОТАЕТ</p>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <span style="font-size: 16px;">1</span>
                                <div style="font-size: 13px; color: var(--text-2);">Выберите дату, количество гостей и время</div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <span style="font-size: 16px;">2</span>
                                <div style="font-size: 13px; color: var(--text-2);">Укажите имя и номер телефона</div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <span style="font-size: 16px;">3</span>
                                <div style="font-size: 13px; color: var(--text-2);">Получите подтверждение брони</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            {{-- Bottom bar --}}
            <div class="bottom-bar">
                <div class="bottom-summary">
                    <strong x-text="selectedSlot ? `${guests} ${guestsWord(guests)} · ${formatSelectedDate()}` : 'Выберите время'"></strong>
                    <span x-show="selectedSlot" x-text="`«{{ $restaurant->name }}» · бронь без предоплаты`"></span>
                </div>
                <button class="btn-primary"
                    :disabled="!selectedSlot"
                    @click="step = 'contact'">
                    Забронировать
                </button>
            </div>
        </div>
    </template>

    {{-- ══════════════════════ CONTACT MODAL ══════════════════════ --}}
    <div class="overlay" x-show="step === 'contact'" x-cloak
         @keydown.escape.window="step = 'select'"
         @click.self="step = 'select'">
        <div class="modal">
            <div class="modal-handle"></div>
            <h2 class="modal-title">Контактные данные</h2>
            <p class="modal-subtitle" x-text="`${guests} ${guestsWord(guests)} · ${formatSelectedDate()} · ${selectedSlot}`"></p>

            <div class="error-msg" x-show="bookingError" x-text="bookingError"></div>

            <div class="field">
                <label>Имя</label>
                <input type="text" x-model="name" placeholder="Иван Иванов" autocomplete="name">
            </div>
            <div class="field">
                <label>Телефон</label>
                <input type="tel" x-model="phone" placeholder="+7 900 000-00-00" autocomplete="tel">
            </div>
            <div class="field">
                <label>Комментарий (необязательно)</label>
                <textarea x-model="comment" placeholder="Особые пожелания, аллергии…"></textarea>
            </div>

            <button class="btn-primary"
                style="width: 100%; padding: 15px;"
                :disabled="submitting || !name.trim() || !phone.trim()"
                @click="submitBooking()">
                <span x-show="!submitting">Подтвердить бронирование</span>
                <span x-show="submitting">Создаём бронь…</span>
            </button>
        </div>
    </div>

</div>

<script>
function booking(slug, timezone, restaurantName, address) {

    // Дата YYYY-MM-DD в timezone ресторана (не UTC браузера)
    function dateInTz(tz, offsetDays = 0) {
        const d = new Date();
        d.setDate(d.getDate() + offsetDays);
        return d.toLocaleDateString('en-CA', { timeZone: tz }); // en-CA → YYYY-MM-DD
    }

    // Текущий час в timezone ресторана
    function hourInTz(tz) {
        return parseInt(new Date().toLocaleTimeString('en-US', {
            timeZone: tz, hour: '2-digit', hour12: false
        }), 10);
    }

    return {
        slug,
        timezone,
        restaurantName,
        address,

        // state
        guests:       2,
        selectedDate: dateInTz(timezone),
        todayStr:     dateInTz(timezone),
        tomorrowStr:  dateInTz(timezone, 1),
        weekDays:     [],
        period:       'all',
        allSlots:     [],
        selectedSlot: null,
        loadingSlots: false,
        slotsHint:    '',

        // contact form
        step:         'select',
        name:         '',
        phone:        '',
        comment:      '',
        submitting:   false,
        bookingError: null,

        // confirmation
        confirmation: null,

        // computed
        get filteredSlots() {
            if (this.period === 'all') return this.allSlots;
            return this.allSlots.filter(s => {
                const h = parseInt(s.split(':')[0]);
                return this.period === 'day' ? h < 17 : h >= 17;
            });
        },

        // init
        init() {
            this.buildWeekDays();
            this.autoPeriod();
            this.loadSlots();

            this.$watch('guests', () => { this.selectedSlot = null; this.loadSlots(); });
        },

        // выбирает период по текущему часу в timezone ресторана
        autoPeriod() {
            if (this.selectedDate !== this.todayStr) {
                this.period = 'all';
                return;
            }
            const h = hourInTz(this.timezone);
            if (h >= 17)      this.period = 'evening';
            else if (h >= 12) this.period = 'all';
            else               this.period = 'day';
        },

        buildWeekDays() {
            const names = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
            const todayStr = this.todayStr;
            this.weekDays = Array.from({ length: 14 }, (_, i) => {
                const ds  = dateInTz(timezone, i);
                const d   = new Date(ds + 'T12:00:00'); // полдень чтобы избежать DST
                return {
                    date: ds,
                    name: names[d.getDay()],
                    num:  d.getDate(),
                    past: ds < todayStr,
                };
            });
        },

        setDate(date) {
            this.selectedDate = date;
            this.selectedSlot = null;
            this.autoPeriod();
            this.loadSlots();
        },

        async loadSlots() {
            this.loadingSlots = true;
            this.allSlots = [];
            try {
                const r = await fetch(`/book/${this.slug}/slots`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        date:             this.selectedDate,
                        guests_count:     this.guests,
                        duration_minutes: 120,
                    }),
                });
                const data = await r.json();
                this.allSlots = data.slots || [];

                // Подсказка: сегодня часть слотов скрыта из-за прошедшего времени
                if (this.selectedDate === this.todayStr && this.allSlots.length > 0) {
                    const first = this.allSlots[0];
                    const h = parseInt(first.split(':')[0]);
                    this.slotsHint = h > 11
                        ? `Слоты до ${first} уже прошли`
                        : '';
                } else {
                    this.slotsHint = '';
                }
            } catch {
                this.allSlots = [];
                this.slotsHint = '';
            } finally {
                this.loadingSlots = false;
            }
        },

        async submitBooking() {
            this.bookingError = null;
            this.submitting   = true;
            try {
                const r = await fetch(`/book/${this.slug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        name:         this.name,
                        phone:        this.phone,
                        comment:      this.comment,
                        date:         this.selectedDate,
                        time:         this.selectedSlot,
                        guests_count: this.guests,
                    }),
                });
                const data = await r.json();
                if (!r.ok) {
                    this.bookingError = data.message || 'Ошибка при создании брони.';
                    return;
                }
                this.confirmation = data;
                this.step = 'confirmed';
            } catch {
                this.bookingError = 'Сетевая ошибка. Попробуйте ещё раз.';
            } finally {
                this.submitting = false;
            }
        },

        resetBooking() {
            const today = new Date();
            this.selectedDate = toDateStr(today);
            this.selectedSlot = null;
            this.step         = 'select';
            this.name         = '';
            this.phone        = '';
            this.comment      = '';
            this.confirmation  = null;
            this.bookingError  = null;
            this.loadSlots();
        },

        // helpers
        guestsWord(n) {
            const mod10  = n % 10;
            const mod100 = n % 100;
            if (mod100 >= 11 && mod100 <= 19) return 'гостей';
            if (mod10 === 1) return 'гость';
            if (mod10 >= 2 && mod10 <= 4) return 'гостя';
            return 'гостей';
        },

        formatSelectedDate() {
            if (!this.selectedDate) return '';
            // Добавляем T12:00 чтобы парсинг всегда был в tz ресторана
            const d = new Date(this.selectedDate + 'T12:00:00');
            const days  = ['вс','пн','вт','ср','чт','пт','сб'];
            const months= ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
            return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]}`;
        },
    };
}
</script>
</body>
</html>
