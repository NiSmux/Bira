@extends('layouts.app')

@section('title', (isset($board) ? $board->name . ' - Backlog' : (isset($team) ? $team->name . ' - Team Backlog' : 'Global Backlog')))

@section('content')
    <div class="px-8 py-8 max-w-7xl mx-auto min-h-full flex flex-col">
        <!-- Header -->
        <div class="mb-10 shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-lg border border-primary/20 backdrop-blur-sm">
                            <x-lucide-layers class="w-6 h-6" />
                        </div>
                        @if(isset($board))
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-white/70">{{ $board->name }}</span> <span class="text-primary font-light">Backlog</span>
                        @elseif(isset($team))
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-white/70">{{ $team->name }}</span> <span class="text-primary font-light">Backlog</span>
                        @else
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-white/70">Global</span> <span class="text-primary font-light">Backlog</span>
                        @endif
                    </h2>
                    <p class="text-sm text-muted-foreground mt-3 max-w-2xl leading-relaxed">
                        @if(isset($board))
                            Plan your sprints and manage the backlog for <strong>{{ $board->name }}</strong>. Drag and drop items to prioritize.
                        @elseif(isset($team))
                            Orchestrating unassigned items across all <strong>{{ $team->name }}</strong> boards.
                        @else
                            A unified view of all unassigned items across your workspace.
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button id="toggle-filters-btn" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold transition-all border border-white/10 shadow-lg hover:scale-[1.02] active:scale-[0.98] h-[46px]">
                        <x-lucide-filter class="w-5 h-5" />
                        Filters
                    </button>
                    
                    @if(isset($board) && $board->permissionLevel !== 'viewer')
                        <button data-board-id="{{ $board->id }}" 
                           class="create-task-modal-trigger flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold transition-all border border-white/10 shadow-lg hover:scale-[1.02] active:scale-[0.98] h-[46px]">
                            <x-lucide-plus class="w-5 h-5" />
                            Create Task
                        </button>
                    @endif

                    @if(isset($board) && $board->permissionLevel === 'admin')
                        <button id="new-sprint-trigger-global" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold transition-all shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] h-[46px]">
                            <x-lucide-plus-circle class="w-5 h-5" />
                            Create Sprint
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @include('components.task-filter')

        <div class="flex-1 space-y-12 pb-20">
            @forelse($boards as $backlogBoard)
                @php
                    $permissionLevel = $backlogBoard->permissionLevel;
                    $allSprintsList = collect([$backlogBoard->activeSprint])
                        ->filter()
                        ->concat($backlogBoard->plannedSprints)
                        ->concat($backlogBoard->newSprints);
                @endphp

                <div class="flex flex-col gap-6 board-section" data-board-id="{{ $backlogBoard->id }}">
                    <!-- Board Header (Only show if multiple boards) -->
                    @if(!isset($board))
                        <div class="flex items-center gap-4 pb-4 border-b border-white/5">
                            <h3 class="text-xl font-bold text-white tracking-tight hover:text-primary transition-colors flex items-center gap-3">
                                <a href="{{ route('boards.show', $backlogBoard->id) }}">
                                    {{ $backlogBoard->name }}
                                </a>
                                <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-border-subtle text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                                    {{ $backlogBoard->team->name }}
                                </span>
                            </h3>
                            
                            <div class="ml-auto flex items-center gap-3">
                                @if($permissionLevel === 'admin')
                                    <button class="new-sprint-btn-board px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white text-xs font-bold transition-all border border-white/10" data-board-id="{{ $backlogBoard->id }}">
                                        New Sprint
                                    </button>
                                @endif
                                <button data-board-id="{{ $backlogBoard->id }}" 
                                   class="create-task-modal-trigger px-3 py-1.5 rounded-lg bg-primary/10 hover:bg-primary/20 text-primary text-xs font-bold transition-all border border-primary/20 flex items-center gap-1.5">
                                    <x-lucide-plus class="w-3.5 h-3.5" />
                                    Create Task
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Sprint Creation Form (Hidden) -->
                    @if($permissionLevel === 'admin')
                    <div id="new-sprint-form-{{ $backlogBoard->id }}" class="hidden mb-6 bg-white/[0.02] border border-white/10 rounded-3xl p-8 backdrop-blur-md shadow-2xl sprint-creation-card animate-in fade-in slide-in-from-top-4 duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center text-violet-400">
                                <x-lucide-zap class="w-5 h-5" />
                            </div>
                            <h4 class="text-white text-lg font-bold">Launch New Sprint</h4>
                        </div>
                        <form action="{{ route('boards.sprints.store', $backlogBoard->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-muted-foreground uppercase tracking-[0.1em] mb-2">Sprint Name *</label>
                                    <input type="text" name="name" required placeholder="e.g. Q2 Delivery - Phase 1"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-muted-foreground uppercase tracking-[0.1em] mb-2">Start Date</label>
                                    <input type="date" name="start_date"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-muted-foreground uppercase tracking-[0.1em] mb-2">End Date</label>
                                    <input type="date" name="end_date"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-muted-foreground uppercase tracking-[0.1em] mb-2">Sprint Goal</label>
                                    <textarea name="goal" rows="2" placeholder="Define the primary objective for this sprint..."
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white placeholder:text-muted-foreground/30 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium resize-none"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-3 justify-end">
                                <button type="button" class="cancel-new-sprint px-6 py-2.5 bg-white/5 hover:bg-white/10 text-white text-sm font-bold rounded-xl transition-all border border-white/5" data-board-id="{{ $backlogBoard->id }}">Dismiss</button>
                                <button type="submit" class="px-8 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-primary/20">Create Sprint</button>
                            </div>
                        </form>
                    </div>
                    @endif

                    <!-- Sprints List -->
                    <div class="space-y-4">
                        @foreach($allSprintsList as $sprint)
                            @php
                                $isActive = $sprint->status === 'in_progress';
                                $isPlanned = $sprint->status === 'planned';
                                $isNew = $sprint->status === 'new';
                                
                                $cardBg = $isActive 
                                    ? 'bg-violet-500/[0.03] border-violet-500/20 shadow-lg shadow-violet-500/[0.02]' 
                                    : ($isPlanned ? 'bg-white/[0.02] border-white/10' : 'bg-white/[0.01] border-white/5');
                                
                                $statusBadge = match($sprint->status) {
                                    'in_progress' => 'bg-violet-500/15 text-violet-400 border border-violet-500/30',
                                    'planned'     => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                                    'new'         => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
                                    default       => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
                                };

                                $sprintItems = $sprint->items;
                                $totalCount = $sprintItems->count();
                                $totalPoints = $sprintItems->sum('story_points');
                                
                                // Backlog items for this board
                                $availableBacklogItems = $backlogBoard->backlogTasks;
                            @endphp

                            <div class="sprint-card group border rounded-3xl overflow-hidden transition-all duration-300 {{ $cardBg }}" id="sprint-{{ $sprint->id }}" data-board-id="{{ $backlogBoard->id }}">
                                <!-- Sprint Header -->
                                <div class="flex items-center gap-4 px-6 py-5 cursor-pointer hover:bg-white/[0.02] transition-colors" onclick="toggleSprintCollapse('{{ $sprint->id }}')">
                                    <button class="sprint-toggle-btn text-muted-foreground hover:text-white transition-transform duration-300" id="chevron-{{ $sprint->id }}">
                                        <x-lucide-chevron-down class="w-5 h-5" />
                                    </button>

                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-3">
                                            <span class="text-white font-bold text-lg leading-tight">{{ $sprint->name }}</span>
                                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $statusBadge }}">
                                                {{ str_replace('_', ' ', $sprint->status) }}
                                            </span>
                                        </div>
                                        @if($sprint->start_date || $sprint->end_date)
                                            <span class="text-xs text-muted-foreground/60 font-medium mt-1">
                                                {{ $sprint->start_date?->format('M d') ?? 'TBD' }} — {{ $sprint->end_date?->format('M d, Y') ?? 'TBD' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="ml-auto flex items-center gap-6">
                                        <div class="flex items-center gap-4 text-xs font-bold">
                                            <span class="text-muted-foreground/50 uppercase tracking-widest">{{ $totalCount }} items</span>
                                            @if($totalPoints > 0)
                                                <span class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-white border border-white/5">{{ $totalPoints }}</span>
                                            @endif
                                        </div>

                                        @if($permissionLevel === 'admin')
                                            <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                                @if($isNew)
                                                    <form method="POST" action="{{ route('boards.sprints.plan', [$backlogBoard->id, $sprint->id]) }}">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 text-xs font-black transition-all border border-blue-500/20 uppercase tracking-tighter">
                                                            Plan
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($isNew || $isPlanned)
                                                    <form method="POST" action="{{ route('boards.sprints.start', [$backlogBoard->id, $sprint->id]) }}">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 rounded-xl bg-violet-500/10 hover:bg-violet-500/20 text-violet-400 text-xs font-black transition-all border border-violet-500/20 uppercase tracking-tighter shadow-sm">
                                                            Start Sprint
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($isActive)
                                                    <form method="POST" action="{{ route('boards.sprints.complete', [$backlogBoard->id, $sprint->id]) }}" onsubmit="return confirm('Complete sprint? Unfinished items will return to backlog.')">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-black transition-all border border-amber-500/20 uppercase tracking-tighter">
                                                            Complete
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <button class="edit-sprint-btn p-2 rounded-xl hover:bg-white/10 text-muted-foreground hover:text-white transition-all"
                                                    data-sprint-id="{{ $sprint->id }}"
                                                    data-board-id="{{ $backlogBoard->id }}"
                                                    data-name="{{ $sprint->name }}"
                                                    data-goal="{{ $sprint->goal }}"
                                                    data-start="{{ $sprint->start_date?->format('Y-m-d') }}"
                                                    data-end="{{ $sprint->end_date?->format('Y-m-d') }}">
                                                    <x-lucide-square-pen class="w-4 h-4" />
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Sprint Body -->
                                <div class="sprint-content border-t border-white/5" id="sprint-body-{{ $sprint->id }}">
                                    @if($sprint->goal)
                                        <div class="px-8 py-4 bg-white/[0.01] border-b border-white/5 text-sm text-white/70 italic font-medium">
                                            <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest not-italic mr-3 opacity-50">Sprint Goal:</span>
                                            "{{ $sprint->goal }}"
                                        </div>
                                    @endif

                                    <div class="sprint-items-container min-h-[40px] divide-y divide-white/[0.03]" data-sprint-id="{{ $sprint->id }}" data-board-id="{{ $backlogBoard->id }}">
                                        @forelse($sprintItems as $item)
                                            @include('boards.tasks._task_row', ['item' => $item, 'board' => $backlogBoard, 'permissionLevel' => $permissionLevel, 'inSprint' => true])
                                        @empty
                                            <div class="px-8 py-10 text-center text-muted-foreground/40 text-sm font-medium">
                                                No items in this sprint. Drag here to add.
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- Add items button -->
                                    @if($permissionLevel !== 'viewer')
                                        <div class="px-8 py-4 bg-white/[0.01]">
                                            <button class="add-task-to-sprint-btn flex items-center gap-2 text-xs font-bold text-muted-foreground hover:text-white transition-colors" data-sprint-id="{{ $sprint->id }}" data-board-id="{{ $backlogBoard->id }}">
                                                <x-lucide-plus class="w-4 h-4" />
                                                Add from backlog
                                            </button>
                                            
                                            <div id="add-panel-{{ $sprint->id }}" class="hidden mt-4 animate-in fade-in slide-in-from-top-2 duration-200">
                                                @if($availableBacklogItems->isEmpty())
                                                    <p class="text-[10px] font-bold text-muted-foreground/40 uppercase tracking-widest py-2">Backlog is empty</p>
                                                @else
                                                    <div class="flex items-center justify-between mb-3 border-b border-white/5 pb-2">
                                                        <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Select items (<span class="selected-count-{{ $sprint->id }}">0</span>)</span>
                                                        <button class="confirm-add-to-sprint px-3 py-1 bg-violet-500 hover:bg-violet-600 text-white text-[10px] font-black rounded-lg transition-all disabled:opacity-30 disabled:grayscale uppercase tracking-widest" 
                                                                data-sprint-id="{{ $sprint->id }}" disabled>
                                                            Move to Sprint
                                                        </button>
                                                    </div>
                                                    <div class="max-h-60 overflow-y-auto pr-2 space-y-1 custom-scrollbar">
                                                        @foreach($availableBacklogItems as $bItem)
                                                            <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-all cursor-pointer border border-transparent hover:border-white/5 group/cb">
                                                                <input type="checkbox" class="sprint-item-checkbox rounded bg-white/5 border-white/10 text-primary focus:ring-primary/40 focus:ring-offset-0" 
                                                                       data-item-id="{{ $bItem->id }}" data-sprint-id="{{ $sprint->id }}">
                                                                <span class="text-white/80 text-sm font-medium flex-1 truncate group-hover/cb:text-white transition-colors">{{ $bItem->title }}</span>
                                                                @if($bItem->story_points)
                                                                    <span class="text-[10px] font-black text-muted-foreground bg-white/5 px-2 py-0.5 rounded-md min-w-[24px] text-center">{{ $bItem->story_points }}</span>
                                                                @endif
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Backlog Section -->
                    <div class="mt-4 flex flex-col gap-4">
                        <div class="flex items-center gap-4 px-2">
                            <h4 class="text-sm font-black text-white/50 uppercase tracking-[0.2em] flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full bg-white/20"></div>
                                Backlog
                            </h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-white/5 text-[10px] font-black text-muted-foreground border border-white/5">
                                {{ $backlogBoard->backlogTasks->count() }}
                            </span>
                        </div>

                        {{-- Backlog Action Bar --}}
                        <div id="backlog-bar-{{ $backlogBoard->id }}" class="hidden mb-2 px-6 py-3 bg-primary/10 border border-primary/20 rounded-2xl flex items-center justify-between animate-in fade-in zoom-in-95 duration-200">
                            <span class="text-xs font-black text-primary uppercase tracking-widest"><span class="backlog-selected-count-{{ $backlogBoard->id }}">0</span> items selected</span>
                            <div class="flex items-center gap-3">
                                <select class="backlog-sprint-target bg-background/50 border border-white/10 rounded-xl px-4 py-2 text-white text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
                                    <option value="" disabled selected>Move to sprint...</option>
                                    @foreach($allSprintsList as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <button class="move-backlog-to-sprint px-5 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-black transition-all shadow-lg shadow-primary/20" data-board-id="{{ $backlogBoard->id }}">
                                    Move
                                </button>
                            </div>
                        </div>

                        <div class="backlog-container bg-white/[0.01] border border-dashed border-white/10 rounded-3xl overflow-hidden min-h-[100px] mb-8" data-board-id="{{ $backlogBoard->id }}">
                            @forelse($backlogBoard->backlogTasks as $item)
                                @include('boards.tasks._task_row', ['item' => $item, 'board' => $backlogBoard, 'permissionLevel' => $permissionLevel, 'inBacklog' => true])
                            @empty
                                <div class="w-full py-16 flex flex-col items-center justify-center text-muted-foreground/30">
                                    <x-lucide-package class="w-12 h-12 mb-4 opacity-20" />
                                    <p class="text-sm font-bold uppercase tracking-widest">Clean Slate</p>
                                    <p class="text-xs mt-1">No items in the backlog for this board.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="w-full py-32 flex flex-col items-center justify-center bg-white/[0.02] border border-dashed border-white/10 rounded-[3rem] text-center px-10">
                    <div class="w-24 h-24 rounded-[2.5rem] bg-white/5 flex items-center justify-center text-muted-foreground/20 mb-8 border border-white/5">
                        <x-lucide-archive class="w-12 h-12" />
                    </div>
                    <h3 class="text-2xl font-black text-white mb-3">Your workspace is quiet</h3>
                    <p class="text-muted-foreground max-w-md mx-auto mb-10 leading-relaxed">No backlog items are available. Start by adding tasks to your boards or creating new sprints to organize your work.</p>
                    <a href="{{ route('boards.index') }}"
                        class="inline-flex items-center gap-3 px-10 py-4 rounded-2xl bg-primary hover:bg-primary/90 text-white font-black transition-all shadow-2xl shadow-primary/30 hover:scale-[1.05] active:scale-95">
                        Explorer Boards
                        <x-lucide-arrow-right class="w-5 h-5" />
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Edit Sprint Modal (Reused) -->
    <div id="edit-sprint-modal" class="fixed inset-0 z-[100] items-center justify-center hidden">
        <div class="absolute inset-0 bg-background/80 backdrop-blur-md" id="edit-sprint-backdrop"></div>
        <div class="relative bg-sidebar border border-white/10 rounded-[2.5rem] p-10 w-full max-w-xl mx-4 shadow-3xl animate-in zoom-in-95 duration-200">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                    <x-lucide-square-pen class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-white font-black text-2xl tracking-tight">Configure Sprint</h3>
                    <p class="text-muted-foreground text-sm font-medium">Update details and schedules for your sprint</p>
                </div>
            </div>
            <form id="edit-sprint-form" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2.5">Sprint Name *</label>
                        <input type="text" name="name" id="edit-sprint-name" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2.5">Start Date</label>
                        <input type="date" name="start_date" id="edit-sprint-start"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2.5">End Date</label>
                        <input type="date" name="end_date" id="edit-sprint-end"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2.5">Sprint Goal</label>
                        <textarea name="goal" id="edit-sprint-goal" rows="4"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-white/[0.08] transition-all text-sm font-medium resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-4 justify-end">
                    <button type="button" id="edit-sprint-cancel" class="px-8 py-3 bg-white/5 hover:bg-white/10 text-white text-sm font-black rounded-2xl transition-all border border-white/5 uppercase tracking-widest">Cancel</button>
                    <button type="submit" class="px-10 py-3 bg-primary hover:bg-primary/90 text-white text-sm font-black rounded-2xl transition-all shadow-lg shadow-primary/20 uppercase tracking-widest">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Create Task Modal -->
    <div id="create-task-modal" class="fixed inset-0 z-[100] items-center justify-center hidden">
        <div class="absolute inset-0 bg-background/80 backdrop-blur-md" id="create-task-backdrop"></div>
        <div class="relative bg-sidebar border border-white/10 rounded-[2.5rem] p-10 w-full max-w-2xl mx-4 shadow-3xl animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                    <x-lucide-plus class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-white font-black text-2xl tracking-tight">Create New Task</h3>
                    <p class="text-muted-foreground text-sm font-medium">Add a new item to your backlog</p>
                </div>
            </div>
            <div id="create-task-modal-content">
                <!-- Form moves here -->
                <div class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tag Edit Modal (Reused for task form tags) --}}
    <div id="tag-edit-modal" class="fixed inset-0 z-[110] items-center justify-center p-4 bg-black/60 backdrop-blur-sm hidden">
        <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-in zoom-in duration-200">
            <h3 class="text-white font-bold text-lg mb-5">Edit Tag</h3>
            <div class="space-y-4">
                <input type="hidden" id="edit_tag_id">
                <input type="hidden" id="edit_tag_board_id">
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Tag name</label>
                    <input type="text" id="edit_tag_name" required maxlength="80"
                        class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Color</label>
                    <input type="color" id="edit_tag_color" required
                        class="w-full h-12 border border-border-subtle p-1 bg-background rounded-xl cursor-pointer">
                </div>
                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" id="close-tag-edit-btn" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
                    <button type="button" id="save-tag-edit-btn" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg transition-colors">Save updates</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); padding: 5px; }
    .flatpickr-day { color: rgba(255,255,255,0.7); border-radius: 10px; height: 36px; line-height: 36px; }
    .flatpickr-day:hover { background: rgba(255,255,255,0.05); color: #fff; }
    .flatpickr-day.selected { background: #8b5cf6 !important; border: none; font-weight: bold; }
    .flatpickr-months .flatpickr-month { color: #fff; }
    .flatpickr-current-month .flatpickr-monthDropdown-months { font-weight: 700; }
    .flatpickr-weekday { color: rgba(255,255,255,0.3); font-size: 10px; font-weight: 800; text-transform: uppercase; }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    .sprint-toggle-btn.rotate-180 { transform: rotate(180deg); }
    .sprint-content.collapsed { display: none; }
</style>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function toggleSprintCollapse(id) {
        const body    = document.getElementById('sprint-body-' + id);
        const chevron = document.getElementById('chevron-' + id);
        body.classList.toggle('collapsed');
        chevron.querySelector('svg').classList.toggle('rotate-180');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        // ── Date pickers ──────────────────────────────────────────────────
        flatpickr('input[type="date"]', {
            dateFormat: 'Y-m-d',
            theme: 'dark'
        });

        // ── Sprint creation toggle ────────────────────────────────────────
        const globalTrigger = document.getElementById('new-sprint-trigger-global');
        if (globalTrigger) {
            globalTrigger.addEventListener('click', () => {
                // If single board, trigger its form
                const form = document.querySelector('[id^="new-sprint-form-"]');
                form?.classList.toggle('hidden');
            });
        }

        document.querySelectorAll('.new-sprint-btn-board').forEach(btn => {
            btn.addEventListener('click', () => {
                const boardId = btn.dataset.boardId;
                document.getElementById('new-sprint-form-' + boardId).classList.toggle('hidden');
            });
        });

        document.querySelectorAll('.cancel-new-sprint').forEach(btn => {
            btn.addEventListener('click', () => {
                const boardId = btn.dataset.boardId;
                document.getElementById('new-sprint-form-' + boardId).classList.add('hidden');
            });
        });

        // ── Sprint "Add from Backlog" Panel ───────────────────────────────
        document.querySelectorAll('.add-task-to-sprint-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const sprintId = btn.dataset.sprintId;
                document.getElementById('add-panel-' + sprintId).classList.toggle('hidden');
            });
        });

        document.querySelectorAll('.sprint-item-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const sprintId = cb.dataset.sprintId;
                const panel = document.getElementById('add-panel-' + sprintId);
                const checked = panel.querySelectorAll('.sprint-item-checkbox:checked').length;
                panel.querySelector(`.selected-count-${sprintId}`).textContent = checked;
                panel.querySelector('.confirm-add-to-sprint').disabled = checked === 0;
            });
        });

        document.querySelectorAll('.confirm-add-to-sprint').forEach(btn => {
            btn.addEventListener('click', async () => {
                const sprintId = btn.dataset.sprintId;
                const panel = document.getElementById('add-panel-' + sprintId);
                const checked = [...panel.querySelectorAll('.sprint-item-checkbox:checked')];
                const boardId = btn.closest('.sprint-card').dataset.boardId;
                
                btn.disabled = true;
                btn.textContent = 'Moving...';

                for (const cb of checked) {
                    await fetch(`/boards/${boardId}/sprints/${sprintId}/items`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ item_id: cb.dataset.itemId }),
                    });
                }
                location.reload();
            });
        });

        // ── Backlog Multi-Select Logic ────────────────────────────────────
        document.querySelectorAll('.backlog-row-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const boardId = cb.closest('.backlog-container').dataset.boardId;
                const container = cb.closest('.backlog-container');
                const bar = document.getElementById('backlog-bar-' + boardId);
                const checked = container.querySelectorAll('.backlog-row-checkbox:checked').length;
                
                bar.querySelector(`.backlog-selected-count-${boardId}`).textContent = checked;
                bar.classList.toggle('hidden', checked === 0);
            });
        });

        document.querySelectorAll('.move-backlog-to-sprint').forEach(btn => {
            btn.addEventListener('click', async () => {
                const boardId = btn.dataset.boardId;
                const bar = document.getElementById('backlog-bar-' + boardId);
                const sprintId = bar.querySelector('.backlog-sprint-target').value;
                const container = document.querySelector(`.backlog-container[data-board-id="${boardId}"]`);
                const checked = [...container.querySelectorAll('.backlog-row-checkbox:checked')];

                if (!sprintId || !checked.length) return;

                btn.disabled = true;
                btn.textContent = 'Working...';

                for (const cb of checked) {
                    await fetch(`/boards/${boardId}/sprints/${sprintId}/items`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ item_id: cb.dataset.itemId }),
                    });
                }
                location.reload();
            });
        });

        // ── Drag & Drop with SortableJS ───────────────────────────────────
        // Enable dragging between backlog and sprints, and among sprints
        document.querySelectorAll('.sprint-items-container, .backlog-container').forEach(el => {
            new Sortable(el, {
                group: 'tasks-' + el.dataset.boardId,
                animation: 250,
                ghostClass: 'bg-primary/5',
                chosenClass: 'scale-[0.98]',
                dragClass: 'opacity-50',
                onEnd: async (evt) => {
                    const itemId = evt.item.dataset.itemId;
                    const fromSprintId = evt.from.dataset.sprintId;
                    const toSprintId = evt.to.dataset.sprintId;
                    const boardId = evt.to.dataset.boardId || evt.from.dataset.boardId;

                    if (evt.from === evt.to) return; // same container, just reordered (UI only for now)

                    // If moving back to backlog
                    if (!toSprintId) {
                        await fetch(`/boards/${boardId}/sprints/${fromSprintId}/items/${itemId}`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        });
                    } 
                    // If moving to a sprint (from backlog or another sprint)
                    else {
                        await fetch(`/boards/${boardId}/sprints/${toSprintId}/items`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ item_id: itemId }),
                        });
                    }
                    location.reload();
                }
            });
        });

        // ── Edit Sprint Modal ─────────────────────────────────────────────
        const editModal = document.getElementById('edit-sprint-modal');
        const editForm = document.getElementById('edit-sprint-form');
        
        document.querySelectorAll('.edit-sprint-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const sId = btn.dataset.sprintId;
                const bId = btn.dataset.boardId;
                document.getElementById('edit-sprint-name').value = btn.dataset.name || '';
                document.getElementById('edit-sprint-goal').value = btn.dataset.goal || '';
                document.getElementById('edit-sprint-start').value = btn.dataset.start || '';
                document.getElementById('edit-sprint-end').value = btn.dataset.end || '';
                editForm.action = `/boards/${bId}/sprints/${sId}`;
                editModal.classList.remove('hidden');
                editModal.classList.add('flex');
            });
        });

        document.getElementById('edit-sprint-cancel').addEventListener('click', () => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        });
        document.getElementById('edit-sprint-backdrop').addEventListener('click', () => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        });

        // ── Create Task Modal ─────────────────────────────────────────────
        const createTaskModal = document.getElementById('create-task-modal');
        const createTaskContent = document.getElementById('create-task-modal-content');
        
        document.querySelectorAll('.create-task-modal-trigger').forEach(btn => {
            btn.addEventListener('click', async () => {
                const bId = btn.dataset.boardId;
                createTaskModal.classList.remove('hidden');
                createTaskModal.classList.add('flex');
                
                createTaskContent.innerHTML = `
                    <div class="flex justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>
                `;

                try {
                    const response = await fetch(`/boards/${bId}/tasks/create`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await response.text();
                    createTaskContent.innerHTML = html;
                    
                    // Re-initialize any JS needed for the form
                    initTaskForm(createTaskContent);
                } catch (error) {
                    createTaskContent.innerHTML = `<p class="text-red-400">Failed to load form. Please try again.</p>`;
                }
            });
        });


        // ── Tag System Shared Logic ──────────────────────────────────────
        let currentTagContext = { boardId: null, container: null };

        document.getElementById('close-tag-edit-btn')?.addEventListener('click', () => {
            const editModal = document.getElementById('tag-edit-modal');
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
            if (window.exitManagementMode) window.exitManagementMode();
        });

        document.getElementById('save-tag-edit-btn')?.addEventListener('click', async () => {
            const id = document.getElementById('edit_tag_id').value;
            const boardId = document.getElementById('edit_tag_board_id').value;
            const name = document.getElementById('edit_tag_name').value.trim();
            const color = document.getElementById('edit_tag_color').value;
            
            if (!name || !boardId) {
                console.error('Missing name or board context', { name, boardId });
                return;
            }

            try {
                const response = await fetch(`/boards/${boardId}/tags/${id}`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': csrfToken, 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        _method: 'PATCH',
                        name: name, 
                        color: color 
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error response:', errorText);
                    // If we got HTML back, it's likely a redirect or a fatal error
                    if (errorText.includes('<!DOCTYPE html>')) {
                        alert('Server error: Received a web page instead of a data response. The change might have saved, please refresh.');
                    } else {
                        alert('Server error (' + response.status + '): ' + errorText.substring(0, 50));
                    }
                    throw new Error(`Server returned ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    alert('Server error: Response was not JSON. Please refresh.');
                    return;
                }

                const data = await response.json();
                
                if (data.success) {
                    // Update ALL instances of this tag on the page
                    document.querySelectorAll(`.tag-wrapper[data-tag-id="${id}"]`).forEach(wrapper => {
                        wrapper.dataset.tagName = name;
                        wrapper.dataset.tagColor = color;
                        const label = wrapper.querySelector('.tag-label');
                        if (label) {
                            label.style.setProperty('--tag-color', color);
                            label.style.setProperty('--tag-bg', color + '1a');
                            label.style.color = color;
                            const dot = label.querySelector('div');
                            if (dot) dot.style.backgroundColor = color;
                            const nameSpan = label.querySelector('.tag-name');
                            if (nameSpan) nameSpan.textContent = name;
                        }
                    });

                    // Close the modal
                    const modal = document.getElementById('tag-edit-modal');
                    if (modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                    
                    // Exit management mode safely - handle potential closure issues
                    if (typeof window.exitManagementMode === 'function') {
                        try {
                            window.exitManagementMode();
                        } catch (cleanupError) {
                            console.warn('Cleanup warning:', cleanupError);
                            // We don't alert here because the tag is already saved and updated
                        }
                    }
                } else {
                    alert('Server returned success: false. Message: ' + (data.message || 'No message provided'));
                }
            } catch (error) {
                console.error('Full Error Details:', error);
                alert('An error occurred during UI update: ' + error.name + ': ' + error.message);
            }
        });

        function initTaskForm(container) {
            const boardId = container.querySelector('form').action.split('/').slice(-2)[0];

            // Close modal logic
            container.querySelectorAll('.cancel-modal-btn').forEach(btn => {
                btn.addEventListener('click', closeModal);
            });

            // Assignee type buttons
            const typeInput  = container.querySelector('#assignee_type_input');
            const userDiv    = container.querySelector('#assign-user-select');
            const subTeamDiv = container.querySelector('#assign-subteam-select');
            const buttons    = container.querySelectorAll('.assignee-type-btn');

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const type = btn.dataset.type;
                    buttons.forEach(b => {
                        b.classList.remove('bg-white/10', 'border-white/20', 'text-white', 'bg-violet-600/20', 'border-violet-500/30', 'text-violet-400');
                        b.classList.add('bg-white/5', 'border-white/10', 'text-muted-foreground');
                    });
                    if (type === 'sub_team') {
                        btn.classList.add('bg-violet-600/20', 'border-violet-500/30', 'text-violet-400');
                    } else {
                        btn.classList.add('bg-white/10', 'border-white/20', 'text-white');
                    }
                    btn.classList.remove('bg-white/5', 'border-white/10', 'text-muted-foreground');
                    
                    typeInput.value = type === 'none' ? '' : type;
                    if(userDiv) userDiv.classList.toggle('hidden', type !== 'user');
                    if(subTeamDiv) subTeamDiv.classList.toggle('hidden', type !== 'sub_team');
                });
            });

            // ── Tag Management Logic ──────────────────────────────────────
            let tagManagementMode = 'none';
            let selectedTagsForDeletion = new Set();
            const tagsContainer = container.querySelector('#tags-container');

            window.toggleTagManagement = (mode) => {
                const deleteBtn = container.querySelector('#tag-manage-delete-btn');
                const editBtn = container.querySelector('#tag-manage-edit-btn');
                
                if (tagManagementMode === mode) {
                    if (mode === 'delete' && selectedTagsForDeletion.size > 0) {
                        confirmBatchDelete();
                        return;
                    }
                    exitManagementMode();
                    return;
                }

                exitManagementMode();
                tagManagementMode = mode;
                
                if (mode === 'delete') {
                    tagsContainer.classList.add('tag-container-managing-delete');
                    deleteBtn.classList.add('bg-red-500/20', 'border-red-500/50', 'text-red-400');
                    deleteBtn.classList.remove('text-muted-foreground');
                    container.querySelectorAll('.tag-wrapper label').forEach(label => label.addEventListener('click', handleTagManagementClick, { capture: true }));
                } else if (mode === 'edit') {
                    tagsContainer.classList.add('tag-container-managing-edit');
                    editBtn.classList.add('bg-primary/20', 'border-primary/50', 'text-white');
                    editBtn.classList.remove('text-muted-foreground');
                    container.querySelectorAll('.tag-wrapper label').forEach(label => label.addEventListener('click', handleTagManagementClick, { capture: true }));
                }
            };

            window.exitManagementMode = () => {
                const deleteBtn = container.querySelector('#tag-manage-delete-btn');
                const editBtn = container.querySelector('#tag-manage-edit-btn');

                tagsContainer.classList.remove('tag-container-managing-delete', 'tag-container-managing-edit');
                deleteBtn?.classList.remove('bg-red-500/20', 'border-red-500/50', 'text-red-400');
                deleteBtn?.classList.add('text-muted-foreground');
                editBtn?.classList.remove('bg-primary/20', 'border-primary/50', 'text-white');
                editBtn?.classList.add('text-muted-foreground');
                
                container.querySelectorAll('.tag-wrapper label').forEach(label => label.removeEventListener('click', handleTagManagementClick, { capture: true }));
                container.querySelectorAll('.tag-wrapper').forEach(w => w.classList.remove('tag-to-delete'));
                
                selectedTagsForDeletion.clear();
                tagManagementMode = 'none';
            };

            const handleTagManagementClick = (e) => {
                if (tagManagementMode === 'none') return;
                e.preventDefault(); e.stopPropagation();
                
                const wrapper = e.currentTarget.closest('.tag-wrapper');
                const tagId = wrapper.dataset.tagId;
                
                if (tagManagementMode === 'delete') {
                    if (selectedTagsForDeletion.has(tagId)) {
                        selectedTagsForDeletion.delete(tagId);
                        wrapper.classList.remove('tag-to-delete');
                    } else {
                        selectedTagsForDeletion.add(tagId);
                        wrapper.classList.add('tag-to-delete');
                    }
                } else if (tagManagementMode === 'edit') {
                    const boardId = wrapper.dataset.boardId;
                    const tagEditModal = document.getElementById('tag-edit-modal');
                    document.getElementById('edit_tag_id').value = tagId;
                    document.getElementById('edit_tag_board_id').value = boardId;
                    document.getElementById('edit_tag_name').value = wrapper.dataset.tagName;
                    document.getElementById('edit_tag_color').value = wrapper.dataset.tagColor;
                    tagEditModal.classList.remove('hidden');
                    tagEditModal.classList.add('flex');
                }
            };

            window.toggleCustomTagForm = () => {
                const btn = container.querySelector('#show-custom-tag-btn');
                const form = container.querySelector('#custom-tag-form');
                if (form.classList.contains('hidden')) {
                    form.classList.remove('hidden'); form.classList.add('flex'); btn.classList.add('hidden');
                } else {
                    form.classList.add('hidden'); form.classList.remove('flex'); btn.classList.remove('hidden');
                }
            };

            window.saveCustomTag = () => {
                const nameInput = container.querySelector('#new_tag_name');
                const colorInput = container.querySelector('#new_tag_color');
                const name = nameInput.value.trim();
                const color = colorInput.value;

                if (!name) return alert('Tag name cannot be empty');

                fetch(`/boards/${boardId}/tags`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ name, color })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        const tag = data.tag;
                        const btn = container.querySelector('#show-custom-tag-btn');
                        const div = document.createElement('div');
                        div.className = 'tag-wrapper';
                        div.dataset.tagId = tag.id; div.dataset.tagName = tag.name; div.dataset.tagColor = tag.color;
                        div.innerHTML = `
                            <input type="checkbox" id="tag_${tag.id}" name="tags[]" value="${tag.id}" class="hidden tag-checkbox" checked>
                            <label for="tag_${tag.id}" class="tag-label px-3 py-1.5 rounded-lg text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5 hover:opacity-80 border-transparent shadow-sm"
                                   style="--tag-bg: ${tag.color}1a; --tag-color: ${tag.color}; color: ${tag.color};">
                                <div class="w-2.5 h-2.5 rounded-full" style="background-color: ${tag.color}"></div>
                                <span class="tag-name">${tag.name}</span>
                            </label>
                        `;
                        tagsContainer.insertBefore(div, btn);
                        nameInput.value = '';
                        toggleCustomTagForm();
                    }
                });
            };

            const confirmBatchDelete = () => {
                if (!confirm(`Delete ${selectedTagsForDeletion.size} tags?`)) return;
                fetch(`/boards/${boardId}/tags/batch-delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ tag_ids: Array.from(selectedTagsForDeletion) })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        selectedTagsForDeletion.forEach(id => {
                            container.querySelector(`.tag-wrapper[data-tag-id="${id}"]`)?.remove();
                        });
                        exitManagementMode();
                    }
                });
            };

            // SP → hours auto-conversion
            const spInput = container.querySelector('#story_points');
            const hrsInput = container.querySelector('#estimated_hours');
            if (spInput && hrsInput) {
                const rate = parseFloat(spInput.dataset.spRate);
                if (rate) {
                    let userEditedHours = !!hrsInput.value;
                    spInput.addEventListener('input', function() {
                        if (userEditedHours) return;
                        const sp = parseFloat(this.value);
                        hrsInput.value = (!isNaN(sp) && sp >= 0) ? Math.round(sp * rate * 4) / 4 : '';
                    });
                    hrsInput.addEventListener('input', function() { userEditedHours = this.value !== ''; });
                    hrsInput.addEventListener('blur', function() { if (!this.value) userEditedHours = false; });
                }
            }

            // Form submission
            const form = container.querySelector('#create-task-form');
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Creating...';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: { 
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });
                        const result = await response.json();
                        if (result.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (result.message || 'Failed to create task'));
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Create task';
                        }
                    } catch (error) {
                        alert('System error.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Create task';
                    }
                });
            }
        }

        function closeModal() {
            createTaskModal.classList.add('hidden');
            createTaskModal.classList.remove('flex');
        }

        document.getElementById('create-task-backdrop').addEventListener('click', closeModal);
    });
</script>
@endpush