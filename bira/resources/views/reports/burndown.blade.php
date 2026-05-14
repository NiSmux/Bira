@extends('layouts.app')

@section('title', 'Burndown Chart - ' . $board->name)

@section('content')
<style>
    #sprintSelector {
        background-color: #1a1a2e !important;
        color: white !important;
    }
    #sprintSelector option {
        background-color: #1a1a2e;
        color: white;
    }
</style>
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
                        @if($s->status === 'in_progress') (Active) @endif
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
            <div class="flex items-center justify-between mb-6">
                <p class="text-xs text-muted-foreground">
                    {{ $sprint->start_date->format('M d, Y') }}
                    –
                    {{ $sprint->end_date ? $sprint->end_date->format('M d, Y') : 'In progress' }}
                    @if($sprint->goal)
                        &nbsp;·&nbsp; <span class="italic">{{ $sprint->goal }}</span>
                    @endif
                </p>
                <button type="button" id="view-sprint-tasks-btn" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    View Sprint Tasks
                </button>
            </div>

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

{{-- Sprint Tasks Modal --}}
@if(isset($sprintItems) && $sprint)
<div id="sprint-tasks-modal" class="fixed inset-0 z-50 items-center justify-center" style="display:none;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="sprint-tasks-backdrop"></div>
    <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-3xl mx-4 shadow-2xl max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between mb-5 shrink-0">
            <div>
                <h3 class="text-white font-bold text-lg mb-1">{{ $sprint->name }} Tasks</h3>
                <p class="text-xs text-muted-foreground">{{ $sprintItems->count() }} items in this sprint</p>
            </div>
            <button id="sprint-tasks-close" class="p-1.5 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
            @forelse($sprintItems as $item)
                @php
                    $typeName = mb_strtolower($item->type->name ?? '');
                    $border = match(true) {
                        str_contains($typeName, 'story') || str_contains($typeName, 'istorija') => 'border-l-emerald-500',
                        str_contains($typeName, 'bug')   || str_contains($typeName, 'klaida')   => 'border-l-red-500',
                        default => 'border-l-blue-500'
                    };
                    $priCode = mb_strtolower($item->priority->name ?? '');
                    $priStyle = match($priCode) {
                        'urgent', 'skubus' => 'bg-red-500/10 text-red-400',
                        'high', 'aukštas' => 'bg-yellow-500/10 text-yellow-400',
                        'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
                        'low', 'žemas' => 'bg-blue-500/10 text-blue-400',
                        default => 'bg-gray-500/10 text-gray-400'
                    };
                @endphp
                <div class="bg-white/[0.02] border border-white/5 border-l-[3px] {{ $border }} rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $priStyle }} shrink-0 w-24 text-center">
                            {{ $item->priority->name ?? 'None' }}
                        </span>
                        <h5 class="text-white font-medium truncate text-sm">{{ $item->title }}</h5>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        @if($item->story_points)
                            <div class="flex items-center gap-1.5 text-muted-foreground bg-white/5 px-2 py-1 rounded-md text-xs font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                {{ $item->story_points }}
                            </div>
                        @endif
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/5 text-muted-foreground">
                            {{ $statuses[$item->pivot->status_id]->name ?? $item->status->name ?? 'No status' }}
                        </span>
                        <div class="flex -space-x-2 pl-2 border-l border-white/5">
                            @if($item->assignee)
                                <div class="w-7 h-7 rounded-full border-2 border-[#1a1a2e] bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary" title="Assigned to {{ $item->assignee->name }}">
                                    {{ strtoupper(substr($item->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($item->assignee->name, ' ') ?: '', 1, 1)) }}
                                </div>
                            @elseif($item->subTeam)
                                <div class="w-7 h-7 rounded-full border-2 border-[#1a1a2e] bg-violet-500/20 flex items-center justify-center text-[10px] font-bold text-violet-400" title="Assigned to sub-team: {{ $item->subTeam->name }}">
                                    {{ strtoupper(substr($item->subTeam->name, 0, 2)) }}
                                </div>
                            @else
                                <div class="w-7 h-7 rounded-full border-2 border-[#1a1a2e] bg-white/5 flex items-center justify-center text-[10px] font-bold text-muted-foreground" title="Unassigned">--</div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-muted-foreground text-sm opacity-60">
                    No tasks found in this sprint.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endif
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

    // Sprint tasks modal
    const taskModal    = document.getElementById('sprint-tasks-modal');
    const taskBtn      = document.getElementById('view-sprint-tasks-btn');
    const taskClose    = document.getElementById('sprint-tasks-close');
    const taskBackdrop = document.getElementById('sprint-tasks-backdrop');

    if(taskBtn && taskModal) {
        taskBtn.addEventListener('click', () => {
            taskModal.style.display = 'flex';
        });
        
        const closeTaskModal = () => { taskModal.style.display = 'none'; };
        if (taskClose) taskClose.addEventListener('click', closeTaskModal);
        if (taskBackdrop) taskBackdrop.addEventListener('click', closeTaskModal);
    }
</script>
@endpush
