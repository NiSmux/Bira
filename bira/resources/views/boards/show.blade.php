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
                        <span class="px-2 py-0.5 rounded-full bg-white/5 text-[10px] font-bold text-muted-foreground">
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

                <div class="kanban-tasks space-y-4 flex-1 min-h-[500px]" data-status-id="{{ $status->id }}">
                    @foreach($board->items->where('status_id', $status->id) as $item)
                        <div class="group bg-card border border-border-subtle rounded-2xl p-5 hover:border-primary/50 transition-all {{ $permissionLevel !== 'viewer' ? 'cursor-move' : '' }} shadow-sm active:scale-[0.98]" data-id="{{ $item->id }}">
                            <div class="flex items-start justify-between mb-2">
                                <h5 class="text-white font-semibold line-clamp-2 leading-tight">{{ $item->title }}</h5>
                                @if($permissionLevel !== 'viewer')
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 -mr-2">
                                        <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                @if($item->story_points)
                                    <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-[10px] font-bold">
                                        {{ $item->story_points }}
                                    </span>
                                @endif
                                @php
                                    $priorityStyles = match(mb_strtolower($item->priority->name ?? 'Default')) {
                                        'urgent', 'skubus' => 'bg-red-500/10 text-red-400',
                                        'high', 'aukštas' => 'bg-yellow-500/10 text-yellow-400',
                                        'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
                                        'low', 'žemas' => 'bg-blue-500/10 text-blue-400',
                                        default => 'bg-gray-500/10 text-gray-400'
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $priorityStyles }}">
                                    {{ $item->priority->name ?? 'None' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between mt-auto">
                                <div class="flex -space-x-2">
                                    <div class="w-7 h-7 rounded-full border-2 border-card bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary">SC</div>
                                    <div class="w-7 h-7 rounded-full border-2 border-card bg-purple-500/20 flex items-center justify-center text-[10px] font-bold text-purple-400">MJ</div>
                                </div>
                                <a href="{{ route('boards.tasks.show', [$board->id, $item->id]) }}" class="text-muted-foreground hover:text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                        </div>
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
            <span class="px-2.5 py-1 rounded-full bg-white/5 text-xs font-bold text-muted-foreground">
                {{ $board->items->where('status_id', $backlogStatus->id)->count() }} Items
            </span>
        </div>
        
        <div class="kanban-tasks w-full bg-white/[0.01] border border-dashed border-border-subtle rounded-3xl p-6 flex flex-col gap-3 min-h-[150px] transition-colors hover:bg-white/[0.02]" data-status-id="{{ $backlogStatus->id }}">
            @foreach($board->items->where('status_id', $backlogStatus->id) as $item)
                <div class="flex flex-col md:flex-row md:items-center justify-between group bg-card border border-border-subtle rounded-2xl p-4 hover:border-primary/50 transition-all {{ $permissionLevel !== 'viewer' ? 'cursor-move' : '' }} shadow-sm active:scale-[0.99] gap-4" data-id="{{ $item->id }}">
                    <!-- Left Side: Title & Priority -->
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        @php
                            $priorityStyles = match(mb_strtolower($item->priority->name ?? 'Default')) {
                                'urgent', 'skubus' => 'bg-red-500/10 text-red-400',
                                'high', 'aukštas' => 'bg-yellow-500/10 text-yellow-400',
                                'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
                                'low', 'žemas' => 'bg-blue-500/10 text-blue-400',
                                default => 'bg-gray-500/10 text-gray-400'
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $priorityStyles }} shrink-0 w-24 text-center">
                            {{ $item->priority->name ?? 'None' }}
                        </span>
                        
                        <h5 class="text-white font-medium truncate text-base">{{ $item->title }}</h5>
                    </div>

                    <!-- Right Side: Metadata & Actions -->
                    <div class="flex items-center gap-6 shrink-0">
                        @if($item->story_points)
                            <div class="flex items-center gap-2 text-muted-foreground bg-white/5 px-2 py-1 rounded-md text-xs font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                {{ $item->story_points }}
                            </div>
                        @endif

                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-card bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary" title="Assignee Code">SC</div>
                        </div>

                        <div class="flex items-center gap-2 border-l border-white/5 pl-6">
                            @if($permissionLevel !== 'viewer')
                                <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}" class="p-2 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors opacity-0 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                            @endif
                            <a href="{{ route('boards.tasks.show', [$board->id, $item->id]) }}" class="p-2 rounded-lg hover:bg-primary/10 hover:text-primary text-muted-foreground transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($board->items->where('status_id', $backlogStatus->id)->isEmpty())
                <div class="w-full py-8 flex items-center justify-center text-muted-foreground text-sm font-medium opacity-50">
                    Drag items here to send them to the backlog.
                </div>
            @endif
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const permissionLevel = '{{ $permissionLevel }}';

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
