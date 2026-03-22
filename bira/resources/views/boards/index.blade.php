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
        <a href="{{ route('boards.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Create new board
        </a>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($boards as $board)
            <div class="group bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/50 transition-all shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Project</span>
                </div>
                
                <h3 class="text-xl font-bold text-white mb-2">{{ $board->name }}</h3>
                <div class="flex items-center gap-2 mb-6">
                    <p class="text-sm text-muted-foreground">Team: <span class="text-white/80 font-medium">{{ $board->team?->name ?? 'None' }}</span></p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                    <span class="text-xs text-muted-foreground">Created: {{ \Carbon\Carbon::parse($board->created_at)->format('Y-m-d') }}</span>
                    <div class="flex items-center gap-4">
                        @php
                            $isOwner = $board->team && $board->team->members()->where('users.id', Auth::user()->id)->where('team_members.role_in_team', 'owner')->exists();
                        @endphp
                        @if($isOwner)
                            <form action="{{ route('boards.destroy', $board->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this board?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-400 font-bold text-sm transition-colors" title="Delete board">
                                    Delete
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('boards.show', $board->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-primary-light font-bold text-sm transition-colors group-hover:gap-2 transition-all">
                            Open
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-muted-foreground mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">You don't have any boards yet</h3>
                <p class="text-muted-foreground mb-8 text-center max-w-sm">Start by creating your first Kanban board and inviting your team to join.</p>
                <a href="{{ route('boards.create') }}" class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20">
                    Create first board
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection