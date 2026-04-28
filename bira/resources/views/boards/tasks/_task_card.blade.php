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
                        <x-lucide-square-pen class="w-4 h-4" />
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
                <x-lucide-chevron-right class="w-5 h-5" />
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
                    <x-lucide-star class="w-3.5 h-3.5" />
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
                        <x-lucide-square-pen class="w-4 h-4" />
                    </a>
                @endif
                <a href="{{ route('boards.tasks.show', [$board->id ?? $backlogBoard->id, $item->id]) }}" class="p-2 rounded-lg hover:bg-primary/10 hover:text-primary text-muted-foreground transition-all">
                    <x-lucide-chevron-right class="w-5 h-5" />
                </a>
            </div>
        </div>
    </div>
</div>
