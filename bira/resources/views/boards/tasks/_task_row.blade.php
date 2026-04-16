@php
    $typeName = mb_strtolower($item->type->name ?? '');
    $dotColor = match(true) {
        str_contains($typeName, 'story') || str_contains($typeName, 'istorija') => 'bg-emerald-500',
        str_contains($typeName, 'bug')   || str_contains($typeName, 'klaida')   => 'bg-red-500',
        default => 'bg-blue-500',
    };
    $priName  = mb_strtolower($item->priority->name ?? '');
    $priStyle = match($priName) {
        'urgent', 'skubus'    => 'bg-red-500/10 text-red-400',
        'high', 'aukštas'     => 'bg-yellow-500/10 text-yellow-400',
        'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
        'low', 'žemas'        => 'bg-blue-500/10 text-blue-400',
        default               => 'bg-gray-500/10 text-gray-400',
    };
    $statusName = $item->status->name ?? '—';
    $boardMode = $board->estimation_mode ?? ($item->boards->first()->estimation_mode ?? 'points');
    $estimationValue = $boardMode === 'hours' ? $item->estimated_hours : $item->story_points;
    $estimationSuffix = $boardMode === 'hours' ? 'h' : '';
@endphp

<div data-item-id="{{ $item->id }}" 
     class="backlog-row filterable-task group flex items-center gap-4 px-8 py-3.5 hover:bg-white/[0.03] transition-all duration-200 {{ $permissionLevel !== 'viewer' ? 'cursor-grab active:cursor-grabbing' : '' }} border-l-2 border-transparent hover:border-primary/40 relative"
     data-filter-sp="{{ $estimationValue ? $estimationValue . $estimationSuffix : '0' }}"
     data-filter-type="{{ $item->type->name ?? 'none' }}"
     data-filter-assignee="{{ optional($item->assignee)->name ?? (optional($item->subTeam)->name ? 'Subteam: ' . optional($item->subTeam)->name : 'Unassigned') }}"
     data-filter-priority="{{ $item->priority->name ?? 'none' }}"
>
    
    @if($permissionLevel !== 'viewer' && isset($inBacklog))
        <input type="checkbox" class="backlog-row-checkbox shrink-0 rounded-lg bg-white/5 border-white/10 text-primary focus:ring-primary/40 focus:ring-offset-0 w-4 h-4 cursor-pointer" data-item-id="{{ $item->id }}">
    @endif

    <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="w-2.5 h-2.5 rounded-full {{ $dotColor }} shrink-0 shadow-[0_0_8px_rgba(0,0,0,0.3)]"></div>
        
        <div class="flex flex-col min-w-0">
            <span class="text-white text-sm font-bold truncate group-hover:text-primary transition-colors">{{ $item->title }}</span>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest">{{ $item->type->name ?? 'Task' }}</span>
                @if($item->creator)
                    <span class="text-[10px] text-muted-foreground/30">•</span>
                    <span class="text-[10px] text-muted-foreground/40 font-medium">Created by {{ $item->creator->name }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4 shrink-0">
        @if($item->priority)
            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $priStyle }} border border-transparent group-hover:border-current/10 transition-all">
                {{ $item->priority->name }}
            </span>
        @endif

        <span class="px-2.5 py-1 rounded-lg bg-white/5 text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest border border-white/5 min-w-[80px] text-center">
            {{ $statusName }}
        </span>

        @if($estimationValue)
            <div class="min-w-[32px] w-auto h-8 px-1.5 rounded-full bg-white/5 flex items-center justify-center text-[11px] font-black text-white border border-white/5 group-hover:bg-primary/20 group-hover:border-primary/30 transition-all">
                {{ $estimationValue }}{{ $estimationSuffix }}
            </div>
        @else
            <div class="w-8 h-8"></div>
        @endif

        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200">
            @if($permissionLevel !== 'viewer')
                <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}" class="p-2 rounded-xl hover:bg-white/10 text-muted-foreground hover:text-white transition-all" title="Quick Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </a>
            @endif
            <a href="{{ route('boards.tasks.show', [$board->id, $item->id]) }}" class="p-2 rounded-xl hover:bg-white/10 text-muted-foreground hover:text-white transition-all" title="View Details">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>
</div>
