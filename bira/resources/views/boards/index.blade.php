@extends('layouts.app')

@section('title', 'My boards')

@section('hide_sidebar')
@endsection

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">My Kanban boards</h2>
            <p class="text-muted-foreground mt-1">View and manage all your projects</p>
        </div>
        <button id="create-board-trigger" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Create new board
        </button>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    @forelse($groupedBoards as $teamName => $teamBoards)
        <div class="mb-12 last:mb-0">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-1.5 h-8 bg-primary rounded-full"></div>
                <h3 class="text-2xl font-bold text-white">{{ $teamName }}</h3>
                <div class="h-px flex-grow bg-white/5 mx-4"></div>
                <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                    {{ $teamBoards->count() }} {{ \Illuminate\Support\Str::plural('Board', $teamBoards->count()) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($teamBoards as $itemBoard)
                    <div class="group bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/50 transition-all shadow-sm hover:shadow-xl hover:shadow-primary/5 flex flex-col">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </div>
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider bg-white/5 px-2 py-1 rounded">Project</span>
                        </div>
                        
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-primary transition-colors">{{ $itemBoard->name }}</h3>
                        
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex -space-x-2">
                                @foreach($itemBoard->members->take(3) as $member)
                                    <div class="w-7 h-7 rounded-full border-2 border-card bg-primary/20 flex items-center justify-center text-[10px] font-bold text-white" title="{{ $member->name }}">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                @endforeach
                                @if($itemBoard->members->count() > 3)
                                    <div class="w-7 h-7 rounded-full border-2 border-card bg-white/5 flex items-center justify-center text-[10px] font-bold text-muted-foreground">
                                        +{{ $itemBoard->members->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs text-muted-foreground">{{ $itemBoard->members->count() }} members</span>
                        </div>

                        <div class="mt-auto pt-4 border-t border-white/5 flex items-center justify-between">
                            <span class="text-[10px] text-muted-foreground uppercase font-medium tracking-tight">Created: {{ \Carbon\Carbon::parse($itemBoard->created_at)->format('M d, Y') }}</span>
                            <div class="flex items-center gap-3">
                                @php
                                    $isOwner = $itemBoard->team && $itemBoard->team->members()->where('users.id', Auth::user()->id)->where('team_members.role_in_team', 'owner')->exists();
                                @endphp
                                @if($isOwner)
                                    <form action="{{ route('boards.destroy', $itemBoard->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this board?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-muted-foreground hover:text-red-500 transition-colors" title="Delete board">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('boards.show', $itemBoard->id) }}" class="inline-flex items-center gap-1.5 text-primary hover:text-primary-light font-bold text-sm transition-all group-hover:gap-2">
                                    Open
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="py-20 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-muted-foreground mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">You don't have any boards yet</h3>
            <p class="text-muted-foreground mb-8 text-center max-w-sm">Start by creating your first Kanban board and inviting your team to join.</p>
            <button id="create-board-empty-trigger" class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20">
                Create first board
            </button>
        </div>
    @endforelse
</div>

{{-- Board Creation Modal --}}
<div id="board-create-modal" class="fixed inset-0 z-[100] items-center justify-center hidden">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-md" id="board-create-backdrop"></div>
    <div class="relative bg-sidebar border border-white/10 rounded-[2.5rem] p-10 w-full max-w-2xl mx-4 shadow-3xl animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div id="board-create-modal-content">
            <div class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    
    @keyframes zoom-in {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-in.zoom-in-95 {
        animation: zoom-in 0.2s ease-out;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('board-create-modal');
    const content = document.getElementById('board-create-modal-content');
    const backdrop = document.getElementById('board-create-backdrop');
    
    const openModal = async () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        content.innerHTML = `
            <div class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
        `;

        try {
            const response = await fetch("{{ route('boards.create') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            
            // Extract the script from partial manually because innerHTML doesn't execute script tags
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const script = temp.querySelector('script');
            
            content.innerHTML = html;
            
            if (script) {
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                content.appendChild(newScript);
            }
        } catch (error) {
            content.innerHTML = `<p class="text-red-400">Failed to load form. Please try again.</p>`;
        }
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.getElementById('create-board-trigger')?.addEventListener('click', openModal);
    document.getElementById('create-board-empty-trigger')?.addEventListener('click', openModal);
    backdrop?.addEventListener('click', closeModal);
});
</script>
@endpush
@endsection