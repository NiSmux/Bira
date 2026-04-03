@extends('layouts.app')

@section('title', 'Burndown Chart - ' . $board->name)

@section('content')
<div class="px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h2 class="text-3xl font-bold tracking-tight text-white">Reports</h2>
        <p class="text-sm text-muted-foreground mt-1">{{ $board->name }}</p>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 mb-8 border-b border-white/10">
        <a href="{{ route('reports.burndown', $board->id) }}"
           class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors border-primary text-white">
            Burndown Chart
        </a>
        <a href="{{ route('reports.velocity', $board->id) }}"
           class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-white">
            Velocity Chart
        </a>
    </div>

    @if($sprints->isEmpty())
        {{-- No sprints yet --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <svg class="w-16 h-16 text-muted-foreground/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-muted-foreground text-lg font-medium">No sprint data available</p>
            <p class="text-muted-foreground/60 text-sm mt-1">Start a sprint to begin tracking burndown progress.</p>
        </div>
    @else
        {{-- Sprint Selector --}}
        <div class="flex items-center gap-4 mb-6">
            <label class="text-sm font-medium text-muted-foreground">Sprint</label>
            <select id="sprintSelector"
                    class="bg-white/5 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-pointer">
                @foreach($sprints as $s)
                    <option value="{{ route('reports.burndown', [$board->id, $s->id]) }}"
                        {{ $sprint && $sprint->id === $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                        @if($s->status === 'active') (Active) @endif
                    </option>
                @endforeach
            </select>
        </div>

        @if(!$sprint || !$sprint->start_date)
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm">
                This sprint has not been started yet. Start the sprint to begin burndown tracking.
            </div>
        @elseif(!$chartData || empty($chartData['labels']))
            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-muted-foreground text-sm">
                No chart data available for this sprint.
            </div>
        @else
            {{-- Sprint date range --}}
            <p class="text-xs text-muted-foreground mb-6">
                {{ $sprint->start_date->format('M d, Y') }}
                –
                {{ $sprint->end_date ? $sprint->end_date->format('M d, Y') : 'In progress' }}
                @if($sprint->goal)
                    &nbsp;·&nbsp; <span class="italic">{{ $sprint->goal }}</span>
                @endif
            </p>

            {{-- Stats row --}}
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">Committed</p>
                    <p class="text-3xl font-bold text-white">{{ $chartData['totalPoints'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">story points</p>
                </div>
                <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">Completed</p>
                    <p class="text-3xl font-bold text-green-400">{{ $chartData['completedPoints'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1">story points</p>
                </div>
                <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">Remaining</p>
                    <p class="text-3xl font-bold {{ $chartData['remainingPoints'] > 0 ? 'text-amber-400' : 'text-green-400' }}">
                        {{ $chartData['remainingPoints'] }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-1">story points</p>
                </div>
            </div>

            {{-- Chart Card --}}
            <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center gap-6 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-8 h-0.5 bg-white/30" style="border-top: 2px dashed rgba(255,255,255,0.3)"></span>
                        <span class="text-xs text-muted-foreground">Ideal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-8 h-0.5 bg-primary rounded-full"></span>
                        <span class="text-xs text-muted-foreground">Actual</span>
                    </div>
                </div>
                <div class="relative" style="height: 360px;">
                    <canvas id="burndownChart"></canvas>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if($chartData && !empty($chartData['labels']))
<script>
(function () {
    const labels    = @json($chartData['labels']);
    const actual    = @json($chartData['actual']);
    const ideal     = @json($chartData['ideal']);

    const ctx = document.getElementById('burndownChart').getContext('2d');

    // Gradient fill for actual line
    const gradient = ctx.createLinearGradient(0, 0, 0, 360);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.25)');
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Ideal',
                    data: ideal,
                    borderColor: 'rgba(255, 255, 255, 0.25)',
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0,
                    fill: false,
                },
                {
                    label: 'Actual',
                    data: actual,
                    borderColor: '#8b5cf6',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#1a1a2e',
                    pointBorderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    backgroundColor: gradient,
                    spanGaps: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 15, 25, 0.95)',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: 'rgba(255,255,255,0.6)',
                    padding: 12,
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y !== null ? ctx.parsed.y + ' pts' : '—'}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: 'rgba(255,255,255,0.4)', font: { size: 11 } },
                    border: { color: 'rgba(255,255,255,0.08)' },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: 'rgba(255,255,255,0.4)',
                        font: { size: 11 },
                        callback: v => v + ' pts',
                    },
                    border: { color: 'rgba(255,255,255,0.08)' },
                },
            },
        },
    });
})();
</script>
@endif
<script>
    document.getElementById('sprintSelector')?.addEventListener('change', function () {
        window.location.href = this.value;
    });
</script>
@endpush
