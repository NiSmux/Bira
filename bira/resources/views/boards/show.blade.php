@extends('layouts.app')

@section('title', 'Lenta: ' . $board->name)

@section('content')
<div class="px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $board->name }}</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ $board->team->name }}</p>
            </div>
            <a href="{{ route('boards.tasks.createTask', $board->id) }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Nauja užduotis
            </a>
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
                        <div class="column-title-container flex items-center gap-2 group/title cursor-pointer">
                            <h4 class="column-name text-xs font-bold uppercase tracking-widest text-muted-foreground group-hover/title:text-white transition-colors" data-id="{{ $status->id }}">{{ $status->name }}</h4>
                            <input type="text" class="column-name-input hidden bg-white/5 border border-white/10 rounded px-2 py-0.5 text-xs font-bold uppercase tracking-widest text-white focus:outline-none focus:ring-1 focus:ring-primary/50 w-32" value="{{ $status->name }}">
                            <svg class="w-3 h-3 text-muted-foreground/0 group-hover/title:text-muted-foreground transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-white/5 text-[10px] font-bold text-muted-foreground">
                            {{ $board->items->where('status_id', $status->id)->count() }}
                        </span>
                    </div>
                </div>

                <div class="kanban-tasks space-y-4 flex-1 min-h-[500px]" data-status-id="{{ $status->id }}">
                    @foreach($board->items->where('status_id', $status->id) as $item)
                        <div class="group bg-card border border-border-subtle rounded-2xl p-5 hover:border-primary/50 transition-all cursor-move shadow-sm active:scale-[0.98]" data-id="{{ $item->id }}">
                            <div class="flex items-start justify-between mb-2">
                                <h5 class="text-white font-semibold line-clamp-2 leading-tight">{{ $item->title }}</h5>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 -mr-2">
                                    <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                </div>
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
                                    {{ $item->priority->name ?? 'Nėra' }}
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
                <p class="text-muted-foreground">Šiai lentai nėra sukonfigūruota jokia eiga.</p>
            </div>
        @endforelse

        <!-- Add Column Button -->
        <div class="w-80 shrink-0">
            <div id="add-column-trigger" class="group w-full h-12 flex items-center justify-center gap-2 bg-white/5 border border-dashed border-white/20 rounded-xl cursor-pointer hover:bg-white/10 hover:border-primary/50 transition-all">
                <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-sm font-medium text-muted-foreground group-hover:text-primary transition-colors">Pridėti skiltį</span>
            </div>

            <div id="add-column-form" class="hidden w-full bg-white/5 border border-white/5 rounded-3xl p-4 flex-col h-fit">
                <form action="{{ route('boards.columns.store', $board->id) }}" method="POST">
                    @csrf
                    <input type="text" name="name" placeholder="Skilties pavadinimas..." required
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 mb-3">
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                            Išsaugoti
                        </button>
                        <button type="button" id="cancel-add-column" class="px-3 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                            Atšaukti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                            alert('Klaida perkeliant užduotį.');
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Sistemos klaida.');
                        location.reload();
                    });
                }
            });
        });

        // Column Reordering
        const columnsContainer = document.getElementById('kanban-columns-container');
        if (columnsContainer) {
            new Sortable(columnsContainer, {
                animation: 150,
                draggable: '.kanban-column', // Only actual columns are draggable
                filter: '.column-name-input, input, button', // Don't trigger drag on inputs or buttons
                preventOnFilter: false, // Allow default actions (like focus) on filtered elements
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
                            alert('Klaida pervadinant skiltis.');
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Sistemos klaida.');
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
                            alert('Klaida pervadinant skiltį.');
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
    });
</script>
@endpush
