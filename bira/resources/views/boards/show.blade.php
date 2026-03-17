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
    <div class="flex gap-6 overflow-x-auto pb-8 scrollbar-hide">
        @forelse($statuses as $status)
            <div class="w-80 shrink-0">
                <div class="flex items-center justify-between mb-4 px-2">
                    <div class="flex items-center gap-3">
                        <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $status->name }}</h4>
                        <span class="px-2 py-0.5 rounded-full bg-white/5 text-[10px] font-bold text-muted-foreground">
                            {{ $board->items->where('status_id', $status->id)->count() }}
                        </span>
                    </div>
                    <button class="text-muted-foreground hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </button>
                </div>

                <div class="kanban-tasks space-y-4 min-h-[500px]" data-status-id="{{ $status->id }}">
                    @foreach($board->items->where('status_id', $status->id) as $item)
                        <div class="group bg-card border border-border-subtle rounded-2xl p-5 hover:border-primary/50 transition-all cursor-move shadow-sm active:scale-[0.98]" data-id="{{ $item->id }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex flex-wrap gap-2">
                                    @if($item->story_points)
                                        <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-[10px] font-bold">
                                            {{ $item->story_points }}
                                        </span>
                                    @endif
                                    @php
                                        $priorityColor = match(mb_strtolower($item->priority->name ?? 'Default')) {
                                            'high', 'skubus' => 'red',
                                            'medium', 'vidutinis' => 'amber',
                                            'low', 'žemas' => 'emerald',
                                            default => 'gray'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded bg-{{ $priorityColor }}-500/10 text-{{ $priorityColor }}-400 text-[10px] font-bold uppercase tracking-wider">
                                        {{ $item->priority->name ?? 'Nėra' }}
                                    </span>
                                </div>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                    <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                </div>
                            </div>

                            <h5 class="text-white font-semibold mb-4 line-clamp-2">{{ $item->title }}</h5>

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
    });
</script>
@endpush
