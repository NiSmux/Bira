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
                            {{ $roleLabels[(string)$userRole] ?? ucwords(str_replace('_', ' ', (string)$userRole)) }}
                        @else
                            Viewer
                        @endif
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button id="toggle-filters-btn" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-colors border border-white/10" title="Toggle Filters">
                    <x-lucide-filter class="w-5 h-5" />
                    Filters
                </button>

                <a href="{{ route('boards.sprints.history', $board->id) }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-colors border border-white/10" title="Sprint history">
                    <x-lucide-history class="w-5 h-5" />
                    Sprint History
                </a>
                @if($permissionLevel === 'admin')
                    <a href="{{ route('boards.settings', $board->id) }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-colors border border-white/10" title="Board settings">
                        <x-lucide-settings class="w-5 h-5" />
                        Settings
                    </a>
                @endif
            </div>
        </div>
    </div>

    @include('components.task-filter')

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    @if(!$activeSprint)
        <div class="mb-8 p-6 rounded-[2rem] bg-amber-500/5 border border-amber-500/10 flex flex-col md:flex-row items-center justify-between gap-6 backdrop-blur-sm">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 shadow-inner">
                    <x-lucide-alert-triangle class="w-7 h-7" />
                </div>
                <div>
                    <h3 class="text-amber-200 font-bold text-lg">No Active Sprint</h3>
                    <p class="text-amber-200/50 text-sm font-medium">Start a sprint from the <a href="{{ route('backlog.index', ['board_id' => $board->id]) }}" class="text-amber-500 hover:underline">backlog</a> to see tasks here.</p>
                </div>
            </div>
            <a href="{{ route('backlog.index', ['board_id' => $board->id]) }}" class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-2xl transition-all shadow-lg shadow-amber-500/20 uppercase tracking-widest text-xs">Go to Backlog</a>
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
                                <x-lucide-square-pen class="w-3 h-3 text-muted-foreground/0 group-hover/title:text-muted-foreground transition-colors" />
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
                            <x-lucide-x class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>

                <div class="kanban-tasks column-tasks space-y-4 flex-1 min-h-[500px]" data-status-id="{{ $status->id }}">
                    @if($activeSprint)
                        @foreach($activeSprint->items->where('status_id', $status->id) as $item)
                            @include('boards.tasks._task_card', ['item' => $item, 'board' => $board, 'permissionLevel' => $permissionLevel])
                        @endforeach
                    @endif
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
                        <x-lucide-plus class="w-5 h-5 text-muted-foreground group-hover:text-primary transition-colors" />
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


</div>

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

@endsection

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


