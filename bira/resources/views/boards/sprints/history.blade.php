@extends('layouts.app')

@section('title', 'Sprint History: ' . $board->name)

@section('content')
<div class="px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('boards.show', $board->id) }}" class="text-muted-foreground hover:text-white transition-colors text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                {{ $board->name }}
            </a>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-white">Sprint History</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ $board->team->name }} &mdash; all sprints</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-white/5 text-xs font-bold text-muted-foreground border border-white/10">
                {{ $sprints->count() }} sprint{{ $sprints->count() !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold">&times;</button>
        </div>
    @endif

    @if($sprints->isEmpty())
        <div class="py-20 flex flex-col items-center justify-center text-muted-foreground text-sm opacity-50">
            <svg class="w-12 h-12 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            No sprints found for this board yet.
        </div>
    @else

    {{-- Sprint list --}}
    <div class="space-y-3" id="sprint-history-list">
        @foreach($sprints as $sprint)
        @php
            $statusLabel = match($sprint->status) {
                'new'             => 'New',
                'planned'         => 'Planned',
                'in_progress'     => 'In Progress',
                'to_be_released'  => 'To be Released',
                'delivered'       => 'Delivered',
                default           => ucfirst($sprint->status),
            };
            $statusBadge = match($sprint->status) {
                'new'             => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                'planned'         => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                'in_progress'     => 'bg-violet-500/15 text-violet-400 border-violet-500/30',
                'to_be_released'  => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                'delivered'       => 'bg-green-500/10 text-green-400 border-green-500/20',
                default           => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            };
            $cardBorder = match($sprint->status) {
                'in_progress'    => 'border-violet-500/20',
                'to_be_released' => 'border-amber-500/20',
                'delivered'      => 'border-white/5',
                default          => 'border-white/10',
            };
            $totalItems      = $sprint->items->count();
            $totalPoints     = $sprint->total_points ?? $sprint->items->sum('story_points');
            $completedPoints = $sprint->completed_points ?? 0;
        @endphp

        <div class="sprint-history-card border rounded-2xl overflow-hidden bg-white/[0.02] {{ $cardBorder }}" id="history-sprint-{{ $sprint->id }}">

            {{-- Sprint row (clickable header) --}}
            <button class="history-toggle w-full flex items-center gap-4 px-5 py-4 hover:bg-white/[0.02] transition-colors text-left"
                data-target="history-body-{{ $sprint->id }}">

                {{-- Chevron --}}
                <svg class="history-chevron w-4 h-4 text-muted-foreground shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>

                {{-- Sprint name --}}
                <span class="text-white font-semibold text-base flex-1 text-left">{{ $sprint->name }}</span>

                {{-- Status badge --}}
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusBadge }}">
                    {{ $statusLabel }}
                </span>

                {{-- Dates --}}
                <span class="text-xs text-muted-foreground hidden sm:inline w-36 text-right shrink-0">
                    @if($sprint->start_date || $sprint->end_date)
                        {{ $sprint->start_date?->format('M d, Y') ?? '?' }}
                        @if($sprint->end_date)
                            &nbsp;&rarr;&nbsp;{{ $sprint->end_date->format('M d, Y') }}
                        @endif
                    @else
                        No dates set
                    @endif
                </span>

                {{-- Story points --}}
                <span class="text-xs font-bold text-muted-foreground hidden sm:inline w-24 text-right shrink-0">
                    @if($sprint->status === 'delivered' || $sprint->status === 'to_be_released')
                        {{ $completedPoints }}/{{ $totalPoints }} pts
                    @else
                        {{ $totalPoints }} pts
                    @endif
                </span>

                {{-- Item count --}}
                <span class="text-xs text-muted-foreground hidden sm:inline w-16 text-right shrink-0">
                    {{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }}
                </span>

                {{-- Admin actions --}}
                @if($permissionLevel === 'admin' && $sprint->status === 'to_be_released')
                <form method="POST" action="{{ route('boards.sprints.deliver', [$board->id, $sprint->id]) }}" class="inline" onclick="event.stopPropagation()">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-green-400 text-xs font-bold transition-colors border border-green-500/20 shrink-0">
                        Mark Delivered
                    </button>
                </form>
                @endif
            </button>

            {{-- Sprint body (hidden by default) --}}
            <div class="history-body hidden border-t border-white/5" id="history-body-{{ $sprint->id }}">

                {{-- Sprint meta --}}
                @if($sprint->goal)
                <div class="px-5 py-3 text-sm text-muted-foreground border-b border-white/[0.04]">
                    <span class="text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider mr-2">Goal:</span>
                    {{ $sprint->goal }}
                </div>
                @endif

                {{-- Progress bar for completed sprints --}}
                @if(in_array($sprint->status, ['delivered', 'to_be_released']) && $totalPoints > 0)
                @php $pct = round(($completedPoints / $totalPoints) * 100); @endphp
                <div class="px-5 py-3 border-b border-white/[0.04]">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-muted-foreground shrink-0">{{ $pct }}% complete &middot; {{ $completedPoints }}/{{ $totalPoints }} pts</span>
                    </div>
                </div>
                @endif

                {{-- Task list --}}
                @if($sprint->items->isEmpty())
                    <div class="px-5 py-6 text-center text-muted-foreground text-sm opacity-50">
                        No tasks in this sprint.
                    </div>
                @else
                <div class="divide-y divide-white/[0.03]">
                    @foreach($sprint->items as $item)
                    @php
                        $iTypeName = mb_strtolower($item->type->name ?? '');
                        $dotColor  = match(true) {
                            str_contains($iTypeName, 'story') || str_contains($iTypeName, 'istorija') => 'bg-emerald-500',
                            str_contains($iTypeName, 'bug')   || str_contains($iTypeName, 'klaida')   => 'bg-red-500',
                            default => 'bg-blue-500',
                        };
                        $iPriName   = mb_strtolower($item->priority->name ?? '');
                        $iPriStyle  = match($iPriName) {
                            'urgent', 'skubus'    => 'bg-red-500/10 text-red-400',
                            'high', 'aukštas'     => 'bg-yellow-500/10 text-yellow-400',
                            'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
                            'low', 'žemas'        => 'bg-blue-500/10 text-blue-400',
                            default               => 'bg-gray-500/10 text-gray-400',
                        };
                        $iStatusName = $item->status->name ?? '—';
                    @endphp
                    <a href="{{ route('boards.tasks.show', [$board->id, $item->id]) }}"
                        class="flex items-center gap-3 px-5 py-3 hover:bg-white/[0.02] transition-colors group">
                        <div class="w-2 h-2 rounded-full {{ $dotColor }} shrink-0"></div>
                        <span class="text-white text-sm font-medium flex-1 truncate group-hover:text-primary transition-colors">{{ $item->title }}</span>
                        <span class="text-[10px] font-semibold text-muted-foreground/60 px-1.5 py-0.5 rounded bg-white/5 hidden sm:inline">{{ $iStatusName }}</span>
                        @if($item->priority)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $iPriStyle }} hidden sm:inline">{{ $item->priority->name }}</span>
                        @endif
                        @if($item->assignee)
                        <span class="text-xs text-muted-foreground hidden md:inline">{{ $item->assignee->name }}</span>
                        @endif
                        @if($item->story_points)
                        <span class="text-xs font-bold text-muted-foreground w-6 text-center shrink-0">{{ $item->story_points }}</span>
                        @else
                        <span class="w-6 shrink-0"></span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif

            </div>{{-- end history-body --}}
        </div>{{-- end sprint-history-card --}}
        @endforeach
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.history-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        const body     = document.getElementById(targetId);
        const chevron  = btn.querySelector('.history-chevron');
        if (!body) return;
        const isOpen = !body.classList.contains('hidden');
        body.classList.toggle('hidden', isOpen);
        chevron.style.transform = isOpen ? '' : 'rotate(-180deg)';
    });
});

// Auto-dismiss alerts
document.querySelectorAll('.alert-close').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-container')?.remove());
});
</script>
@endpush
