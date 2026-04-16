@php
    $typeName = mb_strtolower($item->type->name ?? '');
    $cardBorder = match(true) {
        $typeName === 'istorija' || $typeName === 'user story' || $typeName === 'story' => 'border-l-[3px] border-l-emerald-500',
        $typeName === 'užduotis' || $typeName === 'task' => 'border-l-[3px] border-l-blue-500',
        $typeName === 'klaida' || $typeName === 'bug' => 'border-l-[3px] border-l-red-500',
        default => 'border-l-[3px] border-l-transparent'
    };

    $priorityName = mb_strtolower($item->priority->name ?? 'Default');
    $priorityStyles = match($priorityName) {
        'urgent', 'skubus' => 'bg-red-500/10 text-red-400',
        'high', 'aukštas' => 'bg-yellow-500/10 text-yellow-400',
        'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
        'low', 'žemas' => 'bg-blue-500/10 text-blue-400',
        default => 'bg-gray-500/10 text-gray-400'
    };
    $boardMode = $board->estimation_mode ?? ($item->boards->first()->estimation_mode ?? 'points');
    $estimationValue = $boardMode === 'hours' ? $item->estimated_hours : $item->story_points;
    $estimationSuffix = $boardMode === 'hours' ? 'h' : '';
@endphp

<div class="task-card filterable-task group bg-card border border-border-subtle {{ $cardBorder }} rounded-2xl transition-all {{ $permissionLevel !== 'viewer' ? 'cursor-move' : '' }} shadow-sm active:scale-[0.98] hover:border-primary/50" 
     data-id="{{ $item->id }}"
     data-filter-sp="{{ $estimationValue ? $estimationValue . $estimationSuffix : '0' }}"
     data-filter-type="{{ $item->type->name ?? 'none' }}"
     data-filter-assignee="{{ optional($item->assignee)->name ?? (optional($item->subTeam)->name ? 'Subteam: ' . optional($item->subTeam)->name : 'Unassigned') }}"
     data-filter-priority="{{ $item->priority->name ?? 'none' }}"
>
    <!-- Column Variant Layout -->
    <div class="task-card-column flex flex-col p-5 h-full">
        <div class="flex items-start justify-between mb-2">
            <h5 class="text-white font-semibold line-clamp-2 leading-tight">{{ $item->title }}</h5>
            @if($permissionLevel !== 'viewer')
                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 -mr-2">
                    <a href="{{ route('boards.tasks.edit', [$board->id ?? $backlogBoard->id, $item->id]) }}" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @if($estimationValue)
                <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-[10px] font-bold">
                    {{ $estimationValue }}{{ $estimationSuffix }}
                </span>
            @endif
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $priorityStyles }}">
                {{ $item->priority->name ?? 'None' }}
            </span>
            @if(isset($item->tags) && $item->tags->count() > 0)
                @foreach($item->tags as $tag)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border" style="background-color: {{ $tag->color }}1a; color: {{ $tag->color }}; border-color: {{ $tag->color }}4d;" title="{{ $tag->name }}">
                        {{ substr($tag->name, 0, 10) }}{{ strlen($tag->name) > 10 ? '...' : '' }}
                    </span>
                @endforeach
            @endif
        </div>

        <div class="flex items-center justify-between mt-auto">
            <div class="flex -space-x-2">
                @if($item->assignee)
                    <div class="w-7 h-7 rounded-full border-2 border-card bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary" title="Assigned to {{ $item->assignee->name }}">
                        {{ strtoupper(substr($item->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($item->assignee->name, ' ') ?: '', 1, 1)) }}
                    </div>
                @elseif($item->subTeam)
                    <div class="w-7 h-7 rounded-full border-2 border-card bg-violet-500/20 flex items-center justify-center text-[10px] font-bold text-violet-400" title="Assigned to sub-team: {{ $item->subTeam->name }}">
                        {{ strtoupper(substr($item->subTeam->name, 0, 2)) }}
                    </div>
                @else
                    <div class="w-7 h-7 rounded-full border-2 border-card bg-white/5 flex items-center justify-center text-[10px] font-bold text-muted-foreground" title="Unassigned">--</div>
                @endif
            </div>
            <a href="{{ route('boards.tasks.show', [$board->id ?? $backlogBoard->id, $item->id]) }}" class="text-muted-foreground hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>

    <!-- Backlog Variant Layout -->
    <div class="task-card-backlog hidden flex flex-col md:flex-row md:items-center justify-between p-4 md:p-5 gap-4">
        <!-- Left Side: Title & Priority -->
        <div class="flex items-center gap-4 flex-1 min-w-0">
            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $priorityStyles }} shrink-0 w-24 text-center">
                {{ $item->priority->name ?? 'None' }}
            </span>
            <h5 class="text-white font-medium truncate text-base">{{ $item->title }}</h5>
        </div>

        <div class="flex items-center gap-6 shrink-0">
            @if($estimationValue)
                <div class="flex items-center gap-2 text-muted-foreground bg-white/5 px-2 py-1 rounded-md text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    {{ $estimationValue }}{{ $estimationSuffix }}
                </div>
            @endif

            <div class="flex -space-x-2">
                @if($item->assignee)
                    <div class="w-8 h-8 rounded-full border-2 border-card bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary" title="Assigned to {{ $item->assignee->name }}">
                        {{ strtoupper(substr($item->assignee->name, 0, 1)) }}{{ strtoupper(substr(strstr($item->assignee->name, ' ') ?: '', 1, 1)) }}
                    </div>
                @elseif($item->subTeam)
                    <div class="w-8 h-8 rounded-full border-2 border-card bg-violet-500/20 flex items-center justify-center text-[10px] font-bold text-violet-400" title="Assigned to sub-team: {{ $item->subTeam->name }}">
                        {{ strtoupper(substr($item->subTeam->name, 0, 2)) }}
                    </div>
                @else
                    <div class="w-8 h-8 rounded-full border-2 border-card bg-white/5 flex items-center justify-center text-[10px] font-bold text-muted-foreground" title="Unassigned">--</div>
                @endif
            </div>

            <div class="flex items-center gap-2 border-l border-white/5 pl-6">
                @if($permissionLevel !== 'viewer')
                    <a href="{{ route('boards.tasks.edit', [$board->id ?? $backlogBoard->id, $item->id]) }}" class="p-2 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                @endif
                <a href="{{ route('boards.tasks.show', [$board->id ?? $backlogBoard->id, $item->id]) }}" class="p-2 rounded-lg hover:bg-primary/10 hover:text-primary text-muted-foreground transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    </div>
</div>
