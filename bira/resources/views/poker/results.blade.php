@extends('layouts.app')

@section('title', 'Results – {{ $session->title }} – Bira')

@section('content')
<div class="p-8 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('poker.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Sessions
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">{{ $session->title }}</h1>
                <p class="text-muted-foreground mt-1">
                    {{ $session->team->name }} · Results · 
                    @if($session->finished_at)
                        Finished {{ $session->finished_at->diffForHumans() }}
                    @else
                        Created {{ $session->created_at->diffForHumans() }}
                    @endif
                </p>
            </div>
            @if(auth()->id() === $session->created_by)
                <form action="{{ route('poker.savePoints', $session->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-primary/20"
                            onclick="return confirm('Save all consensus points to the work items?')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Points to Tasks
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Results Table --}}
    <div class="bg-card border border-border-subtle rounded-2xl overflow-hidden">
        <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-white/5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
            <div class="col-span-1">#</div>
            <div class="col-span-6">Task</div>
            <div class="col-span-2 text-center">Votes</div>
            <div class="col-span-3 text-center">Consensus</div>
        </div>

        @foreach($results as $index => $result)
            <div class="grid grid-cols-12 gap-4 px-6 py-5 items-center {{ !$loop->last ? 'border-b border-white/5' : '' }} hover:bg-white/[0.02] transition-colors">
                <div class="col-span-1 text-muted-foreground text-sm font-medium">{{ $index + 1 }}</div>
                <div class="col-span-6">
                    <span class="text-white font-medium">{{ $result['workItem']->title }}</span>
                    @if($result['workItem']->description)
                        <p class="text-muted-foreground text-xs mt-0.5 line-clamp-1">{{ $result['workItem']->description }}</p>
                    @endif
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-sm text-muted-foreground">
                        {{ $result['voteCount'] }} / {{ $result['totalMembers'] }}
                    </span>
                </div>
                <div class="col-span-3 flex justify-center">
                    @if($result['consensus'] !== null)
                        <div class="w-12 h-16 rounded-xl bg-primary/10 border-2 border-primary/30 flex items-center justify-center text-primary font-bold text-lg shadow-lg shadow-primary/10">
                            {{ $result['consensus'] }}
                        </div>
                    @else
                        <div class="w-12 h-16 rounded-xl bg-white/5 border-2 border-white/10 flex items-center justify-center text-muted-foreground font-bold text-lg">
                            –
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-3 gap-4 mt-6">
        @php
            $totalPoints = collect($results)->sum('consensus');
            $itemCount = count($results);
            $votedItems = collect($results)->filter(fn($r) => $r['consensus'] !== null)->count();
        @endphp
        <div class="bg-card border border-border-subtle rounded-2xl p-5 text-center">
            <p class="text-muted-foreground text-xs font-semibold uppercase tracking-wider mb-1">Total Points</p>
            <p class="text-3xl font-bold text-white">{{ $totalPoints }}</p>
        </div>
        <div class="bg-card border border-border-subtle rounded-2xl p-5 text-center">
            <p class="text-muted-foreground text-xs font-semibold uppercase tracking-wider mb-1">Items Estimated</p>
            <p class="text-3xl font-bold text-white">{{ $votedItems }} / {{ $itemCount }}</p>
        </div>
        <div class="bg-card border border-border-subtle rounded-2xl p-5 text-center">
            <p class="text-muted-foreground text-xs font-semibold uppercase tracking-wider mb-1">Avg per Item</p>
            <p class="text-3xl font-bold text-white">{{ $votedItems > 0 ? round($totalPoints / $votedItems, 1) : '–' }}</p>
        </div>
    </div>
</div>
@endsection
