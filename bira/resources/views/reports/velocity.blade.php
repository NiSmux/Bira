@extends('layouts.app')

@section('title', 'Velocity Chart - ' . $board->name)

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
           class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-white">
            Burndown Chart
        </a>
        <a href="{{ route('reports.velocity', $board->id) }}"
           class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors border-primary text-white">
            Velocity Chart
        </a>
    </div>

    @if($sprints->isEmpty())
        {{-- No completed sprints yet --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <svg class="w-16 h-16 text-muted-foreground/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-muted-foreground text-lg font-medium">No completed sprints yet</p>
            <p class="text-muted-foreground/60 text-sm mt-1">Complete a sprint to start tracking your team's velocity.</p>
        </div>
    @else
        {{-- Stats row --}}
        @php
            $maxCompleted = count($completed) > 0 ? max($completed) : 0;
            $minCompleted = count($completed) > 0 ? min($completed) : 0;
        @endphp
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-5">
                <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">Average Velocity</p>
                <p class="text-3xl font-bold text-primary">{{ $avgVelocity }}</p>
                <p class="text-xs text-muted-foreground mt-1">pts / sprint</p>
            </div>
            <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-5">
                <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">Highest Sprint</p>
                <p class="text-3xl font-bold text-green-400">{{ $maxCompleted }}</p>
                <p class="text-xs text-muted-foreground mt-1">story points</p>
            </div>
            <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-5">
                <p class="text-xs text-muted-foreground uppercase tracking-widest mb-1">Sprints Completed</p>
                <p class="text-3xl font-bold text-white">{{ $sprints->count() }}</p>
                <p class="text-xs text-muted-foreground mt-1">total sprints</p>
            </div>
        </div>

        {{-- Chart Card --}}
        <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
            <div class="flex items-center gap-6 mb-6">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 rounded-sm bg-white/20"></span>
                    <span class="text-xs text-muted-foreground">Committed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 rounded-sm bg-primary"></span>
                    <span class="text-xs text-muted-foreground">Completed</span>
                </div>
            </div>
            <div class="relative" style="height: 360px;">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>

        {{-- Sprint breakdown table --}}
        <div class="mt-6 bg-white/[0.03] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-widest">Sprint</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-widest">Committed</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-widest">Completed</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-widest">% Done</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($sprints as $s)
                        @php
                            $pct = ($s->total_points ?? 0) > 0
                                ? round(($s->completed_points / $s->total_points) * 100)
                                : 0;
                        @endphp
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-3 text-white font-medium">{{ $s->name }}</td>
                            <td class="px-6 py-3 text-right text-muted-foreground">{{ $s->total_points ?? 0 }}</td>
                            <td class="px-6 py-3 text-right text-white font-semibold">{{ $s->completed_points ?? 0 }}</td>
                            <td class="px-6 py-3 text-right">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $pct >= 80 ? 'bg-green-500/15 text-green-400' : ($pct >= 50 ? 'bg-amber-500/15 text-amber-400' : 'bg-red-500/15 text-red-400') }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if($sprints->isNotEmpty())
<script>
(function () {
    const labels    = @json($labels);
    const committed = @json($committed);
    const completed = @json($completed);
    const avgVel    = {{ $avgVelocity }};

    const ctx = document.getElementById('velocityChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Committed',
                    data: committed,
                    backgroundColor: 'rgba(255, 255, 255, 0.12)',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(139, 92, 246, 0.75)',
                    borderColor: '#8b5cf6',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 1,
                },
                {
                    label: 'Avg Velocity',
                    data: labels.map(() => avgVel),
                    type: 'line',
                    borderColor: 'rgba(251, 191, 36, 0.7)',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    pointRadius: 0,
                    fill: false,
                    order: 0,
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
                        label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y} pts`,
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
@endpush
