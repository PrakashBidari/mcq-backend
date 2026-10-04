@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    @php
        $yen = fn ($amount) => '¥' . number_format($amount);

        // Accent colours for the stat tiles and section cards
        $tones = [
            'purple' => 'bg-purple-100 text-purple-600',
            'blue'   => 'bg-blue-100 text-blue-600',
            'green'  => 'bg-green-100 text-green-600',
            'orange' => 'bg-orange-100 text-orange-600',
            'red'    => 'bg-red-100 text-red-600',
        ];

        $tiles = [];
        if ($canSeePayments) {
            $tiles[] = ['label' => 'Revenue this month', 'value' => $yen($headline['revenue_month']), 'hint' => $yen($headline['revenue_total']) . ' all time', 'icon' => 'yen', 'tone' => 'blue'];
        }
        $tiles[] = ['label' => 'App users', 'value' => number_format($headline['users_total']), 'hint' => '+' . number_format($headline['users_new_month']) . ' this month', 'icon' => 'user-group', 'tone' => 'orange'];
        $tiles[] = ['label' => 'Active users today', 'value' => number_format($headline['active_today']), 'hint' => number_format($headline['active_week']) . ' in the last 7 days', 'icon' => 'bolt', 'tone' => 'green'];
        $tiles[] = ['label' => 'Visitors today', 'value' => number_format($headline['visitors_today']), 'hint' => number_format($headline['visitors_week']) . ' in the last 7 days', 'icon' => 'eye', 'tone' => 'purple'];

        // One chart per measure - each keeps its own scale and its own colour everywhere
        $charts = [];
        if ($canSeePayments) {
            $charts[] = ['key' => 'revenue', 'title' => 'Payments', 'subtitle' => 'Revenue from completed purchases', 'icon' => 'yen'];
        }
        $charts[] = ['key' => 'newUsers', 'title' => 'New users', 'subtitle' => 'App accounts created', 'icon' => 'user-add'];
        $charts[] = ['key' => 'activeUsers', 'title' => 'Active users', 'subtitle' => 'Signed-in users who opened the app', 'icon' => 'bolt'];
        $charts[] = ['key' => 'visitors', 'title' => 'Visitors', 'subtitle' => 'Unique devices that opened the app', 'icon' => 'eye'];
    @endphp

    <!-- Welcome -->
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Welcome back, {{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                Signed in as <span class="font-semibold text-purple-600">{{ ucfirst(auth()->user()->role) }}</span>
                &middot; {{ now()->format('l, F j, Y') }}
            </p>
        </div>
    </div>

    <!-- Headline numbers -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-{{ count($tiles) }}">
        @foreach ($tiles as $tile)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-500">{{ $tile['label'] }}</p>
                        <p class="mt-2 truncate text-3xl font-bold text-gray-800">{{ $tile['value'] }}</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tile['tone']] }}">
                        @include('partials.icon', ['name' => $tile['icon'], 'class' => 'h-6 w-6'])
                    </span>
                </div>
                <p class="mt-3 text-xs text-gray-500">{{ $tile['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Charts -->
    <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm" id="analytics">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tones['purple'] }}">
                    @include('partials.icon', ['name' => 'chart', 'class' => 'h-5 w-5'])
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Analytics</h3>
                    <p class="text-sm text-gray-500" id="rangeCaption">Last 30 days, by day</p>
                </div>
            </div>

            <!-- One range switch drives every chart below -->
            <div class="inline-flex rounded-lg bg-gray-100 p-1" role="group" aria-label="Chart period">
                @foreach (['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $value => $label)
                    <button type="button" data-range="{{ $value }}"
                        class="range-btn rounded-md px-4 py-1.5 text-sm font-semibold text-gray-600 transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-px lg:grid-cols-2">
            @foreach ($charts as $chart)
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="chart-swatch h-9 w-1.5 rounded-full" data-chart-swatch="{{ $chart['key'] }}"></span>
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ $chart['title'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $chart['subtitle'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-800" data-chart-value="{{ $chart['key'] }}">&ndash;</p>
                            <p class="text-xs text-gray-500" data-chart-delta="{{ $chart['key'] }}">&nbsp;</p>
                        </div>
                    </div>
                    <div class="relative mt-4 h-56">
                        <canvas data-chart="{{ $chart['key'] }}" role="img" aria-label="{{ $chart['title'] }} chart"></canvas>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="hidden border-t border-gray-200 px-5 py-3 text-sm text-red-600" id="chartError">
            Couldn't load the chart data. Refresh the page to try again.
        </p>

        <!-- Same numbers as the charts, as a table -->
        <details class="border-t border-gray-200">
            <summary class="cursor-pointer px-5 py-3 text-sm font-semibold text-gray-600">View these numbers as a table</summary>
            <div class="overflow-x-auto px-5 pb-5">
                <table class="w-full min-w-max text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500">
                            <th class="py-2 pr-6 font-semibold">Period</th>
                            @foreach ($charts as $chart)
                                <th class="py-2 pr-6 text-right font-semibold">{{ $chart['title'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="chartTableBody" class="text-gray-700"></tbody>
                </table>
            </div>
        </details>

        <p class="border-t border-gray-200 px-5 py-3 text-xs text-gray-500">
            Active users and visitors are counted from app traffic and start from the day tracking was switched on, so earlier periods show zero.
        </p>
    </div>

    <!-- Areas of the panel -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($sections as $section)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-200 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tones[$section['tone']] }}">
                        @include('partials.icon', ['name' => $section['icon'], 'class' => 'h-5 w-5'])
                    </span>
                    <h3 class="text-lg font-bold text-gray-800">{{ $section['title'] }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($section['items'] as $item)
                        <a href="{{ $item['route'] }}" class="group flex items-center justify-between px-5 py-3 transition hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-600">{{ $item['label'] }}</span>
                            <span class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-800">{{ number_format($item['count']) }}</span>
                                <span class="text-gray-400 transition group-hover:translate-x-0.5">
                                    @include('partials.icon', ['name' => 'arrow-right', 'class' => 'h-4 w-4'])
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const DATA_URL = @json(route('dashboard.chart-data'));

            // Each measure owns one colour (light / dark step of the same hue) and never
            // changes it, whichever period is selected.
            const SERIES = {
                revenue:     { type: 'bar',  light: '#2a78d6', dark: '#3987e5', money: true },
                newUsers:    { type: 'bar',  light: '#eb6834', dark: '#d95926' },
                activeUsers: { type: 'line', light: '#1baf7a', dark: '#199e70' },
                visitors:    { type: 'line', light: '#4a3aa7', dark: '#9085e9' },
            };

            const RANGES = {
                day:   { caption: 'Last 30 days, by day',     unit: 'day' },
                week:  { caption: 'Last 12 weeks, by week',   unit: 'week' },
                month: { caption: 'Last 12 months, by month', unit: 'month' },
                year:  { caption: 'Last 5 years, by year',    unit: 'year' },
            };

            const charts = {};
            let current = null; // last payload from the server
            let range = 'day';
            try { range = localStorage.getItem('dashboard:range') || 'day'; } catch (e) {}
            if (!RANGES[range]) range = 'day';

            const isDark = () => document.documentElement.classList.contains('dark');
            const colorOf = (key) => SERIES[key][isDark() ? 'dark' : 'light'];
            const format = (key, value) =>
                (SERIES[key].money ? '¥' : '') + Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });

            function theme() {
                return isDark()
                    ? { text: '#94a3b8', grid: '#1f2937', surface: '#111827', tooltipBg: '#f8fafc', tooltipText: '#0f172a' }
                    : { text: '#6b7280', grid: '#eef0f3', surface: '#ffffff', tooltipBg: '#111827', tooltipText: '#ffffff' };
            }

            function buildChart(key, labels, values) {
                const canvas = document.querySelector(`[data-chart="${key}"]`);
                if (!canvas) return;

                const t = theme();
                const color = colorOf(key);
                const isLine = SERIES[key].type === 'line';

                if (charts[key]) charts[key].destroy();

                charts[key] = new Chart(canvas, {
                    type: SERIES[key].type,
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            borderColor: color,
                            backgroundColor: isLine ? color + '1f' : color,
                            borderWidth: isLine ? 2 : 0,
                            fill: isLine,
                            tension: 0.3,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: color,
                            pointHoverBorderColor: t.surface,
                            pointHoverBorderWidth: 2,
                            borderRadius: 4,
                            maxBarThickness: 26,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        // Hovering anywhere over a period shows its value - no need to hit the mark
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: t.tooltipBg,
                                titleColor: t.tooltipText,
                                bodyColor: t.tooltipText,
                                padding: 10,
                                displayColors: false,
                                callbacks: { label: (ctx) => format(key, ctx.parsed.y) },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: t.grid },
                                ticks: { color: t.text, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: t.grid },
                                border: { display: false },
                                ticks: {
                                    color: t.text,
                                    precision: 0,
                                    maxTicksLimit: 5,
                                    callback: (value) => format(key, value),
                                },
                            },
                        },
                    },
                });
            }

            // Headline for a chart: the current period's value and how it compares with the one before
            function renderSummary(key, values) {
                const valueEl = document.querySelector(`[data-chart-value="${key}"]`);
                const deltaEl = document.querySelector(`[data-chart-delta="${key}"]`);
                if (!valueEl) return;

                const unit = RANGES[range].unit;
                const now = values[values.length - 1] || 0;
                const before = values[values.length - 2] || 0;
                valueEl.textContent = format(key, now);

                let text;
                if (before === 0 && now === 0) text = `No change vs previous ${unit}`;
                else if (before === 0) text = `▲ up from 0 the previous ${unit}`;
                else {
                    const pct = Math.round(((now - before) / before) * 100);
                    text = pct === 0 ? `No change vs previous ${unit}` : `${pct > 0 ? '▲' : '▼'} ${Math.abs(pct)}% vs previous ${unit}`;
                }
                deltaEl.textContent = `This ${unit} · ${text}`;
            }

            function renderTable(data) {
                const keys = Object.keys(SERIES).filter((key) => Array.isArray(data[key]));
                document.getElementById('chartTableBody').innerHTML = data.labels.map((label, i) =>
                    `<tr class="border-b border-gray-100"><td class="py-2 pr-6">${label}</td>` +
                    keys.map((key) => `<td class="py-2 pr-6 text-right">${format(key, data[key][i])}</td>`).join('') +
                    '</tr>'
                ).reverse().join('');
            }

            function render() {
                if (!current) return;
                Object.keys(SERIES).forEach((key) => {
                    const swatch = document.querySelector(`[data-chart-swatch="${key}"]`);
                    if (swatch) swatch.style.backgroundColor = colorOf(key);
                    if (!Array.isArray(current[key])) return;
                    buildChart(key, current.labels, current[key]);
                    renderSummary(key, current[key]);
                });
                renderTable(current);
            }

            function markActiveRange() {
                document.querySelectorAll('.range-btn').forEach((btn) => {
                    const active = btn.dataset.range === range;
                    btn.classList.toggle('bg-purple-600', active);
                    btn.classList.toggle('text-white', active);
                    btn.classList.toggle('shadow', active);
                    btn.classList.toggle('text-gray-600', !active);
                    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                document.getElementById('rangeCaption').textContent = RANGES[range].caption;
            }

            let requestId = 0;
            function load() {
                const id = ++requestId;
                markActiveRange();
                document.getElementById('chartError').classList.add('hidden');

                fetch(`${DATA_URL}?range=${range}`, { headers: { 'Accept': 'application/json' } })
                    .then((res) => {
                        if (!res.ok) throw new Error(res.status);
                        return res.json();
                    })
                    .then((data) => {
                        if (id !== requestId) return; // a newer period was picked meanwhile
                        current = data;
                        render();
                    })
                    .catch(() => {
                        if (id === requestId) document.getElementById('chartError').classList.remove('hidden');
                    });
            }

            document.querySelectorAll('.range-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    range = btn.dataset.range;
                    try { localStorage.setItem('dashboard:range', range); } catch (e) {}
                    load();
                });
            });

            // Redraw with the other theme's colours when the header switch is used
            window.addEventListener('themechange', render);

            load();
        })();
    </script>
@endpush
