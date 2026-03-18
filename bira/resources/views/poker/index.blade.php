@extends('layouts.app')

@section('title', 'Planning Poker – Bira')

@section('content')
<div class="p-8 max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Planning Poker</h1>
            <p class="text-muted-foreground mt-1">Estimate story points with your team</p>
        </div>
        <a href="{{ route('poker.create', array_filter(['team_id' => request('team_id'), 'board_id' => request('board_id')])) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-primary/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Create Session
        </a>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Sessions List --}}
    @if($sessions->isEmpty())
        <div class="bg-card border border-border-subtle rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-muted-foreground/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <h3 class="text-lg font-semibold text-white mb-2">No sessions yet</h3>
            <p class="text-muted-foreground text-sm">Create your first Planning Poker session to start estimating tasks with your team.</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($sessions as $session)
                <a href="{{ $session->status === 'completed'
                        ? route('poker.results', [$session->id, 'board_id' => request('board_id'), 'team_id' => request('team_id')])
                        : route('poker.show',    [$session->id, 'board_id' => request('board_id'), 'team_id' => request('team_id')]) }}" 
                   class="block bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/30 hover:bg-white/[0.02] transition-all group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary/20 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold group-hover:text-primary transition-colors">{{ $session->title }}</h3>
                                <p class="text-muted-foreground text-sm mt-0.5">
                                    {{ $session->team->name }} · {{ $session->items->count() }} items · by {{ $session->creator->name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-muted-foreground">{{ $session->created_at->diffForHumans() }}</span>
                            @if($session->status === 'active')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/5 text-muted-foreground border border-white/10">Completed</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
