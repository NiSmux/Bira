@extends('layouts.app')

@section('title', 'Board: ' . $board->name)

@section('content')
<div class="px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $board->name }}</h2>
                <div class="flex items-center gap-3 mt-1">
                    <p class="text-sm text-muted-foreground">{{ $board->team->name }}</p>
                    @php
                        $roleLabels = \App\Http\Controllers\BoardController::boardRoles();
                        $roleStyle = match($permissionLevel) {
                            'admin'  => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                            'member' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                            'viewer' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                            default  => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter border {{ $roleStyle }}">
                        @if($userRole)
                            {{ $roleLabels[$userRole] ?? ucwords(str_replace('_', ' ', $userRole)) }}
                        @else
                            Viewer
                        @endif
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($permissionLevel === 'admin')
                    <a href="{{ route('boards.settings', $board->id) }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-colors border border-white/10" title="Board settings">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Settings
                    </a>
                @endif
                @if($permissionLevel !== 'viewer')
                    <a href="{{ route('boards.tasks.createTask', $board->id) }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        New task
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <!-- Kanban Columns -->
    <div id="kanban-columns-container" class="flex gap-6 overflow-x-auto pb-8 scrollbar-hide">
        @forelse($statuses as $status)
            <div class="kanban-column w-80 shrink-0 bg-white/[0.02] border border-white/5 rounded-3xl p-4 flex flex-col h-fit transition-colors hover:bg-white/[0.04]" data-column-id="{{ $status->id }}">
                <div class="flex items-center justify-between mb-6 px-2">
                    <div class="flex items-center gap-3">
                        @if($permissionLevel === 'admin')
                            <div class="column-title-container flex items-center gap-2 group/title cursor-pointer">
                                <h4 class="column-name text-xs font-bold uppercase tracking-widest text-muted-foreground group-hover/title:text-white transition-colors" data-id="{{ $status->id }}">{{ $status->name }}</h4>
                                <input type="text" class="column-name-input hidden bg-white/5 border border-white/10 rounded px-2 py-0.5 text-xs font-bold uppercase tracking-widest text-white focus:outline-none focus:ring-1 focus:ring-primary/50 w-32" value="{{ $status->name }}">
                                <svg class="w-3 h-3 text-muted-foreground/0 group-hover/title:text-muted-foreground transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </div>
                        @else
                            <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $status->name }}</h4>
                        @endif
                        <span class="px-2 py-0.5 rounded-full bg-white/5 text-[10px] font-bold text-muted-foreground mr-2">
                            {{ $board->items->where('status_id', $status->id)->count() }}
                        </span>
                    </div>
                    @if($permissionLevel === 'admin')
                        <button
                            class="delete-column-btn p-1 rounded-lg hover:bg-red-500/10 text-muted-foreground/40 hover:text-red-400 transition-colors"
                            data-column-id="{{ $status->id }}"
                            data-column-name="{{ $status->name }}"
                            title="Delete column"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    @endif
                </div>

                <div class="kanban-tasks column-tasks space-y-4 flex-1 min-h-[500px]" data-status-id="{{ $status->id }}">
                    @foreach($board->items->where('status_id', $status->id) as $item)
                        @include('boards.tasks._task_card', ['item' => $item, 'board' => $board, 'permissionLevel' => $permissionLevel])
                    @endforeach
                </div>
            </div>
        @empty
            <div class="w-full py-12 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                <p class="text-muted-foreground">No workflow is configured for this board.</p>
            </div>
        @endforelse

        {{-- Add Column Button (admin only) --}}
        @if($permissionLevel === 'admin')
            <div class="w-80 shrink-0">
                <div id="add-column-trigger" class="group w-full h-12 flex items-center justify-center gap-2 bg-white/5 border border-dashed border-white/20 rounded-xl cursor-pointer hover:bg-white/10 hover:border-primary/50 transition-all">
                    <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium text-muted-foreground group-hover:text-primary transition-colors">Add column</span>
                </div>

                <div id="add-column-form" class="hidden w-full bg-white/5 border border-white/5 rounded-3xl p-4 flex-col h-fit">
                    <form action="{{ route('boards.columns.store', $board->id) }}" method="POST">
                        @csrf
                        <input type="text" name="name" placeholder="Column name..." required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 mb-3">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                                Save
                            </button>
                            <button type="button" id="cancel-add-column" class="px-3 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Sprint Section -->
    @if($activeSprint || $plannedSprints->isNotEmpty() || $completedSprints->isNotEmpty() || $permissionLevel === 'admin')
    <div class="mt-8 border-t border-white/5 pt-8" id="sprints-section">

        {{-- Section header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold tracking-tight text-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                Sprints
            </h3>
            @if($permissionLevel === 'admin')
                <button id="new-sprint-trigger" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-colors border border-white/10 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    New Sprint
                </button>
            @endif
        </div>

        @if(session('errors') && session('errors')->has('sprint'))
            <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                {{ session('errors')->first('sprint') }}
            </div>
        @endif

        {{-- Create Sprint form --}}
        @if($permissionLevel === 'admin')
        <div id="new-sprint-form" class="hidden mb-6 bg-white/[0.02] border border-white/10 rounded-2xl p-6">
            <h4 class="text-white font-semibold mb-4">Create Sprint</h4>
            <form action="{{ route('boards.sprints.store', $board->id) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Sprint Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Sprint 1"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Start Date</label>
                        <input type="date" name="start_date"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">End Date</label>
                        <input type="date" name="end_date"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Sprint Goal</label>
                        <textarea name="goal" rows="2" placeholder="What is the goal of this sprint?"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" id="cancel-new-sprint" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg transition-colors">Create Sprint</button>
                </div>
            </form>
        </div>
        @endif

        @php
            $allSprintsList = collect([$activeSprint])
                ->filter()
                ->concat($plannedSprints)
                ->concat($completedSprints);
        @endphp

        @foreach($allSprintsList as $sprint)
        @php
            $isActive    = $sprint->status === 'active';
            $isPlanned   = $sprint->status === 'planned';
            $isCompleted = $sprint->status === 'completed';

            $cardBg     = $isActive ? 'bg-violet-500/5 border-violet-500/20' : ($isCompleted ? 'bg-white/[0.01] border-white/5' : 'bg-white/[0.02] border-white/10');
            $statusBadge = match($sprint->status) {
                'active'    => 'bg-violet-500/15 text-violet-400 border border-violet-500/30',
                'planned'   => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                'completed' => 'bg-green-500/10 text-green-400 border border-green-500/20',
            };

            $doneIds = $statuses->where('is_done', 1)->pluck('id');
            $sprintItems   = $sprint->items;
            $doneCount     = $sprintItems->whereIn('status_id', $doneIds->toArray())->count();
            $totalCount    = $sprintItems->count();
            $totalPoints   = $sprintItems->sum('story_points');
            $donePoints    = $sprintItems->whereIn('status_id', $doneIds->toArray())->sum('story_points');

            // Backlog items available to add (no sprint, in backlog status)
            $availableBacklogItems = isset($backlogStatus)
                ? $board->items->where('status_id', $backlogStatus->id)->whereNull('release_id')
                : collect();
        @endphp

        <div class="sprint-card mb-4 border rounded-2xl overflow-hidden {{ $cardBg }}" id="sprint-{{ $sprint->id }}">

            {{-- Sprint header --}}
            <div class="flex items-center gap-3 px-5 py-4">
                {{-- Collapse toggle --}}
                <button class="sprint-toggle text-muted-foreground hover:text-white transition-colors shrink-0" data-sprint="{{ $sprint->id }}">
                    <svg class="w-4 h-4 sprint-chevron transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                {{-- Name --}}
                <span class="text-white font-semibold text-base flex-1">{{ $sprint->name }}</span>

                {{-- Status badge --}}
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusBadge }}">
                    {{ $sprint->status }}
                </span>

                {{-- Dates --}}
                @if($sprint->start_date || $sprint->end_date)
                <span class="text-xs text-muted-foreground hidden sm:inline">
                    {{ $sprint->start_date?->format('M d') ?? '?' }}
                    →
                    {{ $sprint->end_date?->format('M d, Y') ?? '?' }}
                </span>
                @endif

                {{-- Progress (active/completed) --}}
                @if($totalCount > 0 && !$isPlanned)
                <span class="text-xs text-muted-foreground hidden sm:inline">{{ $doneCount }}/{{ $totalCount }} done</span>
                @else
                <span class="text-xs text-muted-foreground hidden sm:inline">{{ $totalCount }} items</span>
                @endif

                {{-- Action buttons --}}
                @if($permissionLevel === 'admin')
                <div class="flex items-center gap-1 ml-2">
                    @if($isPlanned)
                        <form method="POST" action="{{ route('boards.sprints.start', [$board->id, $sprint->id]) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-violet-500/10 hover:bg-violet-500/20 text-violet-400 text-xs font-bold transition-colors border border-violet-500/20">
                                Start Sprint
                            </button>
                        </form>
                    @endif
                    @if($isActive)
                        <form method="POST" action="{{ route('boards.sprints.complete', [$board->id, $sprint->id]) }}" class="inline"
                            onsubmit="return confirm('Complete sprint? Unfinished items will return to backlog.')">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-green-400 text-xs font-bold transition-colors border border-green-500/20">
                                Complete Sprint
                            </button>
                        </form>
                    @endif
                    @if(!$isCompleted)
                        <button class="edit-sprint-btn p-1.5 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors"
                            data-sprint-id="{{ $sprint->id }}"
                            data-name="{{ $sprint->name }}"
                            data-goal="{{ $sprint->goal }}"
                            data-start="{{ $sprint->start_date?->format('Y-m-d') }}"
                            data-end="{{ $sprint->end_date?->format('Y-m-d') }}"
                            title="Edit sprint">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <form method="POST" action="{{ route('boards.sprints.destroy', [$board->id, $sprint->id]) }}" class="inline"
                            onsubmit="return confirm('Delete this sprint? Items will return to backlog.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-500/10 text-muted-foreground hover:text-red-400 transition-colors" title="Delete sprint">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>
                @endif
            </div>

            {{-- Sprint body (collapsible) --}}
            <div class="sprint-body" id="sprint-body-{{ $sprint->id }}">

                {{-- Goal --}}
                @if($sprint->goal)
                <div class="px-5 pb-3 text-sm text-muted-foreground border-t border-white/5 pt-3">
                    <span class="text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider mr-2">Goal:</span>
                    {{ $sprint->goal }}
                </div>
                @endif

                {{-- Progress bar (active sprint) --}}
                @if($isActive && $totalCount > 0)
                @php $pct = round(($doneCount / $totalCount) * 100); @endphp
                <div class="px-5 pb-3 @if(!$sprint->goal) border-t border-white/5 pt-3 @endif">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-violet-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-muted-foreground shrink-0">{{ $pct }}% · {{ $donePoints }}/{{ $totalPoints }} pts</span>
                    </div>
                </div>
                @endif

                {{-- Items list --}}
                <div class="border-t border-white/5">
                    @forelse($sprintItems as $item)
                    @php
                        $iTypeName = mb_strtolower($item->type->name ?? '');
                        $dotColor  = match(true) {
                            str_contains($iTypeName, 'story') || str_contains($iTypeName, 'istorija') => 'bg-emerald-500',
                            str_contains($iTypeName, 'bug')   || str_contains($iTypeName, 'klaida')   => 'bg-red-500',
                            default => 'bg-blue-500',
                        };
                        $iPriName   = mb_strtolower($item->priority->name ?? '');
                        $iPriStyle  = match($iPriName) {
                            'urgent', 'skubus'      => 'bg-red-500/10 text-red-400',
                            'high', 'aukštas'       => 'bg-yellow-500/10 text-yellow-400',
                            'medium', 'vidutinis'   => 'bg-emerald-500/10 text-emerald-400',
                            'low', 'žemas'          => 'bg-blue-500/10 text-blue-400',
                            default                 => 'bg-gray-500/10 text-gray-400',
                        };
                        $iStatusName = $item->status->name ?? '—';
                    @endphp
                    <div class="sprint-item group flex items-center gap-3 px-5 py-3 hover:bg-white/[0.02] transition-colors border-b border-white/[0.03] last:border-0" data-item-id="{{ $item->id }}">
                        <div class="w-2 h-2 rounded-full {{ $dotColor }} shrink-0"></div>
                        <span class="text-white text-sm font-medium flex-1 truncate">{{ $item->title }}</span>
                        <span class="text-[10px] font-semibold text-muted-foreground/60 px-1.5 py-0.5 rounded bg-white/5 hidden sm:inline">{{ $iStatusName }}</span>
                        @if($item->priority)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $iPriStyle }} hidden sm:inline">{{ $item->priority->name }}</span>
                        @endif
                        @if($item->story_points)
                        <span class="text-xs font-bold text-muted-foreground w-6 text-center">{{ $item->story_points }}</span>
                        @else
                        <span class="w-6"></span>
                        @endif
                        @if($permissionLevel !== 'viewer' && !$isCompleted)
                        <button class="remove-sprint-item opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-red-500/10 text-muted-foreground/40 hover:text-red-400 transition-all"
                            data-sprint-id="{{ $sprint->id }}"
                            data-item-id="{{ $item->id }}"
                            title="Remove from sprint">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        @endif
                    </div>
                    @empty
                    <div class="px-5 py-6 text-center text-muted-foreground text-sm opacity-50">
                        No items in this sprint yet.
                    </div>
                    @endforelse
                </div>

                {{-- Add from backlog panel (planned & active sprints, admin/member) --}}
                @if(!$isCompleted && $permissionLevel !== 'viewer')
                <div class="px-5 py-3 border-t border-white/5">
                    <button class="add-items-toggle text-xs font-semibold text-muted-foreground hover:text-white transition-colors flex items-center gap-1.5" data-sprint="{{ $sprint->id }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add items from backlog
                    </button>

                    <div class="add-items-panel hidden mt-3" id="add-panel-{{ $sprint->id }}">
                        @if($availableBacklogItems->isEmpty())
                            <p class="text-xs text-muted-foreground opacity-60 py-2">No items in backlog.</p>
                        @else
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-muted-foreground"><span class="panel-selected-count" data-sprint="{{ $sprint->id }}">0</span> selected</span>
                            <button class="add-selected-to-sprint px-3 py-1.5 rounded-lg bg-violet-500 hover:bg-violet-600 text-white text-xs font-bold transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                data-sprint-id="{{ $sprint->id }}" disabled>
                                Add selected
                            </button>
                        </div>
                        <div class="space-y-0.5 max-h-60 overflow-y-auto pr-1">
                            @foreach($availableBacklogItems as $bItem)
                            @php
                                $bTypeName = mb_strtolower($bItem->type->name ?? '');
                                $bDot = match(true) {
                                    str_contains($bTypeName, 'story') || str_contains($bTypeName, 'istorija') => 'bg-emerald-500',
                                    str_contains($bTypeName, 'bug')   || str_contains($bTypeName, 'klaida')   => 'bg-red-500',
                                    default => 'bg-blue-500',
                                };
                            @endphp
                            <label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
                                <input type="checkbox" class="sprint-panel-checkbox shrink-0 rounded accent-violet-500"
                                    data-item-id="{{ $bItem->id }}"
                                    data-sprint-id="{{ $sprint->id }}">
                                <div class="w-2 h-2 rounded-full {{ $bDot }} shrink-0"></div>
                                <span class="text-white/80 text-sm flex-1 truncate">{{ $bItem->title }}</span>
                                @if($bItem->story_points)
                                <span class="text-xs text-muted-foreground shrink-0">{{ $bItem->story_points }} sp</span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>{{-- end sprint-body --}}
        </div>{{-- end sprint-card --}}
        @endforeach

        @if($allSprintsList->isEmpty() && $permissionLevel === 'admin')
        <div class="py-10 flex flex-col items-center justify-center text-muted-foreground text-sm opacity-50">
            No sprints yet. Create one to start planning.
        </div>
        @endif

    </div>
    @endif

    {{-- Edit Sprint Modal --}}
    <div id="edit-sprint-modal" class="fixed inset-0 z-50 items-center justify-center" style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="edit-sprint-backdrop"></div>
        <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-lg mx-4 shadow-2xl">
            <h3 class="text-white font-bold text-lg mb-5">Edit Sprint</h3>
            <form id="edit-sprint-form" method="POST">
                @csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Sprint Name *</label>
                        <input type="text" name="name" id="edit-sprint-name" required
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Start Date</label>
                        <input type="date" name="start_date" id="edit-sprint-start"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">End Date</label>
                        <input type="date" name="end_date" id="edit-sprint-end"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Sprint Goal</label>
                        <textarea name="goal" id="edit-sprint-goal" rows="3"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" id="edit-sprint-cancel" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Backlog Section -->
    @if(isset($backlogStatus))
    <div class="mt-8 border-t border-white/5 pt-8">
        <div class="flex items-center gap-3 mb-6">
            <h3 class="text-xl font-bold tracking-tight text-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                Backlog
            </h3>
            <span class="px-2.5 py-1 rounded-full bg-white/5 text-xs font-bold text-muted-foreground mr-3">
                {{ $board->items->where('status_id', $backlogStatus->id)->count() }} Items
            </span>

            @php
                $backlogItems = $board->items->where('status_id', $backlogStatus->id);
                $blStoryPoints = $backlogItems->filter(fn($i) => mb_strtolower($i->type->name ?? '') === 'istorija' || mb_strtolower($i->type->name ?? '') === 'user story' || mb_strtolower($i->type->name ?? '') === 'story')->sum('story_points');
                $blTaskPoints = $backlogItems->filter(fn($i) => mb_strtolower($i->type->name ?? '') === 'užduotis' || mb_strtolower($i->type->name ?? '') === 'task')->sum('story_points');
                $blBugPoints = $backlogItems->filter(fn($i) => mb_strtolower($i->type->name ?? '') === 'klaida' || mb_strtolower($i->type->name ?? '') === 'bug')->sum('story_points');
                $blTotalPoints = $backlogItems->sum('story_points');
            @endphp
            <div class="flex items-center gap-1 border-l border-white/10 pl-3">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-500 text-white shadow-sm" title="Bug points">{{ $blBugPoints }}</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-500 text-white shadow-sm" title="Task points">{{ $blTaskPoints }}</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500 text-white shadow-sm" title="Story points">{{ $blStoryPoints }}</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-500/50 text-white shadow-sm ml-1" title="Total backlog points">{{ $blTotalPoints }}</span>
            </div>
        </div>
        
        {{-- Sprint action bar (shown when items are checked) --}}
        @php
            $sprintsForBacklog = collect([$activeSprint])->filter()->concat($plannedSprints);
        @endphp
        @if($permissionLevel !== 'viewer' && $sprintsForBacklog->isNotEmpty())
        <div id="backlog-action-bar" class="hidden mb-3 flex items-center gap-3 px-4 py-2.5 bg-violet-500/10 border border-violet-500/20 rounded-xl">
            <span id="backlog-selected-count" class="text-xs font-semibold text-violet-400">0 selected</span>
            <select id="backlog-sprint-select" class="bg-background border border-border-subtle rounded-lg px-3 py-1.5 text-white text-xs focus:outline-none focus:ring-2 focus:ring-primary/50 ml-auto">
                @foreach($sprintsForBacklog as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->status }})</option>
                @endforeach
            </select>
            <button id="backlog-add-to-sprint-btn" class="px-3 py-1.5 rounded-lg bg-violet-500 hover:bg-violet-600 text-white text-xs font-bold transition-colors">
                Add to sprint
            </button>
        </div>
        @endif

        <div class="kanban-tasks backlog-tasks w-full bg-white/[0.01] border border-dashed border-border-subtle rounded-2xl overflow-hidden min-h-[80px]" data-status-id="{{ $backlogStatus->id }}">
            @forelse($board->items->where('status_id', $backlogStatus->id) as $item)
            @php
                $blTypeName = mb_strtolower($item->type->name ?? '');
                $blDot = match(true) {
                    str_contains($blTypeName, 'story') || str_contains($blTypeName, 'istorija') => 'bg-emerald-500',
                    str_contains($blTypeName, 'bug')   || str_contains($blTypeName, 'klaida')   => 'bg-red-500',
                    default => 'bg-blue-500',
                };
                $blPriName  = mb_strtolower($item->priority->name ?? '');
                $blPriStyle = match($blPriName) {
                    'urgent', 'skubus'    => 'bg-red-500/10 text-red-400',
                    'high', 'aukštas'     => 'bg-yellow-500/10 text-yellow-400',
                    'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
                    'low', 'žemas'        => 'bg-blue-500/10 text-blue-400',
                    default               => 'bg-gray-500/10 text-gray-400',
                };
            @endphp
            <div data-id="{{ $item->id }}" class="backlog-row group flex items-center gap-3 px-4 py-2.5 border-b border-white/[0.04] last:border-0 hover:bg-white/[0.03] transition-colors {{ $permissionLevel !== 'viewer' ? 'cursor-move' : '' }}">
                @if($permissionLevel !== 'viewer' && $sprintsForBacklog->isNotEmpty())
                <input type="checkbox" class="backlog-sprint-checkbox shrink-0 rounded accent-violet-500 cursor-pointer" data-item-id="{{ $item->id }}">
                @endif
                <div class="w-2 h-2 rounded-full {{ $blDot }} shrink-0"></div>
                <span class="text-white text-sm font-medium flex-1 truncate">{{ $item->title }}</span>
                @if($item->priority)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $blPriStyle }} hidden sm:inline">{{ $item->priority->name }}</span>
                @endif
                @if($item->story_points)
                <span class="text-xs font-bold text-muted-foreground w-8 text-right">{{ $item->story_points }} sp</span>
                @endif
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    @if($permissionLevel !== 'viewer')
                    <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                    @endif
                    <a href="{{ route('boards.tasks.show', [$board->id, $item->id]) }}" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
            @empty
                <div class="w-full py-6 flex items-center justify-center text-muted-foreground text-sm font-medium opacity-50">
                    Drag items here to send them to the backlog.
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection

{{-- Delete Column Modal --}}
<div id="delete-column-modal" class="fixed inset-0 z-50 items-center justify-center" style="display:none;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="delete-modal-backdrop"></div>
    <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
        <h3 class="text-white font-bold text-lg mb-2" id="delete-modal-title">Delete Column</h3>
        <p class="text-muted-foreground text-sm mb-6" id="delete-modal-message"></p>
        <div class="flex gap-3 justify-end">
            <button id="delete-modal-cancel" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white text-sm font-medium transition-colors">Cancel</button>
            <button id="delete-modal-confirm" class="px-4 py-2 rounded-lg bg-red-500/80 hover:bg-red-500 text-white text-sm font-bold transition-colors hidden">Delete Column</button>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
    .flatpickr-day { color: rgba(255,255,255,0.7); border-radius: 8px; }
    .flatpickr-day:hover, .flatpickr-day.today:hover { background: rgba(255,255,255,0.1); border-color: transparent; color: #fff; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #7c3aed; border-color: #7c3aed; color: #fff; }
    .flatpickr-day.today { border-color: rgba(124,58,237,0.5); }
    .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: rgba(255,255,255,0.2); }
    .flatpickr-months .flatpickr-month, .flatpickr-current-month, .flatpickr-monthDropdown-months { background: transparent; color: #fff; fill: #fff; }
    .flatpickr-weekday { color: rgba(255,255,255,0.4); }
    .flatpickr-prev-month, .flatpickr-next-month { fill: rgba(255,255,255,0.5) !important; }
    .flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #fff !important; }
    .numInputWrapper span { border-color: rgba(255,255,255,0.1); }
    .numInputWrapper span:hover { background: rgba(255,255,255,0.1); }
    .numInputWrapper input { color: #fff; }
    .flatpickr-monthDropdown-months { background: #1a1a2e; color: #fff; }
</style>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const permissionLevel = '{{ $permissionLevel }}';
        const csrfToken = '{{ csrf_token() }}';

        // ── Date pickers ──────────────────────────────────────────────────
        const fpConfig = {
            dateFormat: 'Y-m-d',
            allowInput: true,
            disableMobile: false,
            theme: 'dark',
        };
        document.querySelectorAll('input[type="date"]').forEach(el => {
            flatpickr(el, fpConfig);
        });

        // ── Sprint section ────────────────────────────────────────────────

        // New sprint form toggle
        const newSprintTrigger = document.getElementById('new-sprint-trigger');
        const newSprintForm    = document.getElementById('new-sprint-form');
        const cancelNewSprint  = document.getElementById('cancel-new-sprint');
        if (newSprintTrigger) {
            newSprintTrigger.addEventListener('click', () => {
                newSprintForm.classList.remove('hidden');
                newSprintTrigger.classList.add('hidden');
                newSprintForm.querySelector('input[name="name"]').focus();
            });
        }
        if (cancelNewSprint) {
            cancelNewSprint.addEventListener('click', () => {
                newSprintForm.classList.add('hidden');
                newSprintTrigger.classList.remove('hidden');
            });
        }

        // Sprint collapse/expand
        document.querySelectorAll('.sprint-toggle').forEach(btn => {
            const sprintId = btn.dataset.sprint;
            const body     = document.getElementById('sprint-body-' + sprintId);
            const chevron  = btn.querySelector('.sprint-chevron');
            btn.addEventListener('click', () => {
                body.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });
        });

        // Add items from backlog toggle
        document.querySelectorAll('.add-items-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const panel = document.getElementById('add-panel-' + btn.dataset.sprint);
                panel.classList.toggle('hidden');
            });
        });

        // Sprint panel checkboxes — update count & enable/disable button
        document.querySelectorAll('.sprint-panel-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const sprintId  = cb.dataset.sprintId;
                const panel     = document.getElementById('add-panel-' + sprintId);
                const checked   = panel.querySelectorAll('.sprint-panel-checkbox:checked').length;
                panel.querySelector('.panel-selected-count').textContent = checked;
                const addBtn    = panel.querySelector('.add-selected-to-sprint');
                addBtn.disabled = checked === 0;
            });
        });

        // Add selected items to sprint (AJAX, sequential)
        document.querySelectorAll('.add-selected-to-sprint').forEach(btn => {
            btn.addEventListener('click', async () => {
                const sprintId = btn.dataset.sprintId;
                const panel    = document.getElementById('add-panel-' + sprintId);
                const checked  = [...panel.querySelectorAll('.sprint-panel-checkbox:checked')];
                if (!checked.length) return;

                btn.disabled = true;
                btn.textContent = 'Adding...';

                for (const cb of checked) {
                    await fetch(`/boards/{{ $board->id }}/sprints/${sprintId}/items`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ item_id: cb.dataset.itemId }),
                    });
                }
                location.reload();
            });
        });

        // Backlog checkboxes — show action bar and update count
        const backlogActionBar    = document.getElementById('backlog-action-bar');
        const backlogSelectedCount = document.getElementById('backlog-selected-count');
        const backlogAddBtn        = document.getElementById('backlog-add-to-sprint-btn');
        const backlogSprintSelect  = document.getElementById('backlog-sprint-select');

        document.querySelectorAll('.backlog-sprint-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const count = document.querySelectorAll('.backlog-sprint-checkbox:checked').length;
                if (backlogSelectedCount) backlogSelectedCount.textContent = count + ' selected';
                if (backlogActionBar) backlogActionBar.classList.toggle('hidden', count === 0);
            });
        });

        if (backlogAddBtn) {
            backlogAddBtn.addEventListener('click', async () => {
                const sprintId = backlogSprintSelect.value;
                const checked  = [...document.querySelectorAll('.backlog-sprint-checkbox:checked')];
                if (!checked.length || !sprintId) return;

                backlogAddBtn.disabled = true;
                backlogAddBtn.textContent = 'Adding...';

                for (const cb of checked) {
                    await fetch(`/boards/{{ $board->id }}/sprints/${sprintId}/items`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ item_id: cb.dataset.itemId }),
                    });
                }
                location.reload();
            });
        }

        // Remove item from sprint (AJAX)
        document.querySelectorAll('.remove-sprint-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId   = btn.dataset.itemId;
                const sprintId = btn.dataset.sprintId;
                const row      = btn.closest('.sprint-item');

                fetch(`/boards/{{ $board->id }}/sprints/${sprintId}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        location.reload();
                    } else {
                        alert('Error removing item.');
                    }
                })
                .catch(() => alert('Network error.'));
            });
        });

        // Edit Sprint modal
        const editModal    = document.getElementById('edit-sprint-modal');
        const editForm     = document.getElementById('edit-sprint-form');
        const editCancel   = document.getElementById('edit-sprint-cancel');
        const editBackdrop = document.getElementById('edit-sprint-backdrop');

        document.querySelectorAll('.edit-sprint-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const sprintId = btn.dataset.sprintId;
                document.getElementById('edit-sprint-name').value  = btn.dataset.name  ?? '';
                document.getElementById('edit-sprint-goal').value  = btn.dataset.goal  ?? '';
                document.getElementById('edit-sprint-start').value = btn.dataset.start ?? '';
                document.getElementById('edit-sprint-end').value   = btn.dataset.end   ?? '';
                editForm.action = `/boards/{{ $board->id }}/sprints/${sprintId}`;
                editModal.style.display = 'flex';
            });
        });

        const closeEditModal = () => { editModal.style.display = 'none'; };
        if (editCancel)   editCancel.addEventListener('click', closeEditModal);
        if (editBackdrop) editBackdrop.addEventListener('click', closeEditModal);

        // Task drag-and-drop (only for members and admins)
        if (permissionLevel !== 'viewer') {
            const columns = document.querySelectorAll('.kanban-tasks');
            columns.forEach(column => {
                new Sortable(column, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    dragClass: 'rotate-2',
                    onEnd: function (evt) {
                        const itemEl = evt.item;
                        const taskId = itemEl.getAttribute('data-id');
                        const newStatusId = evt.to.getAttribute('data-status-id');
                        
                        fetch(`/boards/{{ $board->id }}/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                status_id: newStatusId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Error moving task.');
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('System error.');
                            location.reload();
                        });
                    }
                });
            });
        }

        // Column Reordering (admin only)
        if (permissionLevel === 'admin') {
            const columnsContainer = document.getElementById('kanban-columns-container');
            if (columnsContainer) {
                new Sortable(columnsContainer, {
                    animation: 150,
                    draggable: '.kanban-column',
                    filter: '.column-name-input, input, button',
                    preventOnFilter: false,
                    onEnd: function (evt) {
                        const columnId = evt.item.getAttribute('data-column-id');
                        if (!columnId) return;

                        fetch(`/boards/{{ $board->id }}/columns/${columnId}/reorder`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                new_index: evt.newIndex
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Error reordering columns.');
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('System error.');
                            location.reload();
                        });
                    }
                });
            }

            // Column Renaming
            document.querySelectorAll('.column-title-container').forEach(container => {
                const nameEl = container.querySelector('.column-name');
                const inputEl = container.querySelector('.column-name-input');
                const columnId = nameEl.getAttribute('data-id');

                container.addEventListener('click', () => {
                    nameEl.classList.add('hidden');
                    inputEl.classList.remove('hidden');
                    inputEl.focus();
                    inputEl.setSelectionRange(inputEl.value.length, inputEl.value.length);
                });

                const saveName = () => {
                    const newName = inputEl.value.trim();
                    if (newName && newName !== nameEl.textContent) {
                        fetch(`/boards/{{ $board->id }}/columns/${columnId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ name: newName })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                nameEl.textContent = newName;
                                nameEl.classList.remove('hidden');
                                inputEl.classList.add('hidden');
                            } else {
                                alert('Error renaming column.');
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            location.reload();
                        });
                    } else {
                        nameEl.classList.remove('hidden');
                        inputEl.classList.add('hidden');
                        inputEl.value = nameEl.textContent;
                    }
                };

                inputEl.addEventListener('blur', saveName);
                inputEl.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        inputEl.blur();
                    } else if (e.key === 'Escape') {
                        inputEl.value = nameEl.textContent;
                        inputEl.blur();
                    }
                });
            });

            // Add Column Toggling
            const addTrigger = document.getElementById('add-column-trigger');
            const addForm = document.getElementById('add-column-form');
            const cancelBtn = document.getElementById('cancel-add-column');

            if (addTrigger && addForm && cancelBtn) {
                addTrigger.addEventListener('click', () => {
                    addTrigger.classList.add('hidden');
                    addForm.classList.remove('hidden');
                    addForm.querySelector('input').focus();
                });

                cancelBtn.addEventListener('click', () => {
                    addForm.classList.add('hidden');
                    addTrigger.classList.remove('hidden');
                });
            }

            // Delete Column
            const deleteModal      = document.getElementById('delete-column-modal');
            const deleteModalMsg   = document.getElementById('delete-modal-message');
            const deleteModalTitle = document.getElementById('delete-modal-title');
            const deleteCancelBtn  = document.getElementById('delete-modal-cancel');
            const deleteConfirmBtn = document.getElementById('delete-modal-confirm');

            let pendingDeleteColumnId   = null;
            let pendingDeleteColumnEl   = null;

            const openDeleteModal = (msg, showConfirm, title = 'Delete Column') => {
                deleteModalTitle.textContent = title;
                deleteModalMsg.textContent   = msg;
                deleteConfirmBtn.classList.toggle('hidden', !showConfirm);
                deleteModal.style.display = 'flex';
            };

            const closeDeleteModal = () => {
                deleteModal.style.display = 'none';
                pendingDeleteColumnId = null;
                pendingDeleteColumnEl = null;
            };

            document.querySelectorAll('.delete-column-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const columnId   = btn.getAttribute('data-column-id');
                    const columnName = btn.getAttribute('data-column-name');
                    const columnEl   = btn.closest('.kanban-column');

                    fetch(`/boards/{{ $board->id }}/columns/${columnId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (response.ok && data.success) {
                            columnEl.remove();
                        } else if (data.has_tasks) {
                            openDeleteModal(
                                data.message,
                                false,
                                `Cannot delete "${columnName}"`
                            );
                        } else {
                            openDeleteModal('An unexpected error occurred. Please try again.', false, 'Error');
                        }
                    })
                    .catch(() => {
                        openDeleteModal('Network error. Please try again.', false, 'Error');
                    });
                });
            });

            deleteCancelBtn.addEventListener('click', closeDeleteModal);
            document.getElementById('delete-modal-backdrop').addEventListener('click', closeDeleteModal);
            deleteConfirmBtn.addEventListener('click', closeDeleteModal);
        }
    });
</script>
@endpush
