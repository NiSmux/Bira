@extends('layouts.app')

@section('title', '{{ $session->title }} – Planning Poker – Bira')

@section('content')
<div class="p-8 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Planning Poker</h1>
            <p class="text-muted-foreground mt-1">Estimate story points with your team</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Timer --}}
            <div id="timer-pill" class="flex items-center gap-2 px-4 py-2 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 font-mono font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="timer-value">{{ gmdate('i:s', $session->remainingSeconds()) }}</span>
            </div>

            {{-- Reveal / Complete (only for creator) --}}
            @if(auth()->id() === $session->created_by)
                <form action="{{ route('poker.complete', $session->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-primary/20"
                            onclick="return confirm('Are you sure you want to reveal all cards and finish the session?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Reveal Cards
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

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Current Item Card --}}
    @if($currentItem)
        <div class="bg-card border border-border-subtle rounded-2xl p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-primary uppercase tracking-wider">{{ $currentItem->workItem->type->name ?? 'Task' }}</span>
                    <h2 class="text-xl font-bold text-white mt-1">{{ $currentItem->workItem->title }}</h2>
                    <p class="text-muted-foreground text-sm mt-2">Vote on the complexity of this {{ strtolower($currentItem->workItem->type->name ?? 'task') }}</p>
                </div>
                @if($currentItem->workItem->description)
                    <div class="ml-4 max-w-xs">
                        <p class="text-muted-foreground text-sm">{{ Str::limit($currentItem->workItem->description, 150) }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Participants --}}
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-3">Participants ({{ $members->count() }})</h3>
            <div class="flex items-center gap-3 flex-wrap">
                @php
                    $colors = ['bg-violet-500', 'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-pink-500', 'bg-indigo-500'];
                @endphp
                @foreach($members as $index => $member)
                    @php
                        $hasVoted = $currentItem->votes->where('user_id', $member->id)->count() > 0;
                        $colorClass = $colors[$index % count($colors)];
                    @endphp
                    <div class="flex flex-col items-center gap-1">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full {{ $colorClass }} flex items-center justify-center text-white text-xs font-bold shadow-lg">
                                {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strstr($member->name, ' ') ?: '', 1, 1)) }}
                            </div>
                            @if($hasVoted)
                                <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center shadow-lg">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @endif
                        </div>
                        <span class="text-xs text-muted-foreground">{{ Str::before($member->name, ' ') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Voting Cards --}}
        <div class="mb-8">
            <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-4">Select Your Estimate</h3>
            <div class="grid grid-cols-5 sm:grid-cols-9 gap-3">
                @php
                    $userVote = $currentItem->votes->where('user_id', $user->id)->first();
                @endphp
                @foreach($fibonacciCards as $card)
                    <form action="{{ route('poker.vote', [$session->id, $currentItem->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="points" value="{{ $card === '?' ? '' : $card }}">
                        <button type="submit" 
                                class="w-full aspect-[3/4] rounded-2xl border-2 font-bold text-2xl transition-all hover:scale-105 hover:shadow-xl active:scale-95
                                {{ ($userVote && (($card === '?' && $userVote->points === null) || ($card !== '?' && $userVote->points == $card)))
                                    ? 'bg-primary border-primary text-white shadow-lg shadow-primary/30'
                                    : 'bg-white/5 border-white/10 text-white hover:border-primary/50 hover:bg-white/10' }}">
                            @if($card === '?')
                                <span class="text-xl">?</span>
                            @else
                                {{ $card }}
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Item Navigation --}}
    <div class="bg-card border border-border-subtle rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-4">All Items</h3>
        <div class="space-y-2">
            @foreach($session->items as $item)
                @php
                    $isCurrent = $currentItem && $item->id === $currentItem->id;
                    $userVotedThis = $item->hasUserVoted($user->id);
                    $allVoted = $session->allVotedForItem($item);
                @endphp
                <div class="flex items-center justify-between p-3 rounded-xl {{ $isCurrent ? 'bg-primary/10 border border-primary/20' : 'hover:bg-white/5' }} transition-all">
                    <div class="flex items-center gap-3">
                        @if($allVoted)
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        @elseif($userVotedThis)
                            <div class="w-6 h-6 rounded-full bg-blue-500/20 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        @else
                            <div class="w-6 h-6 rounded-full bg-white/5 border border-white/10"></div>
                        @endif
                        <span class="text-sm {{ $isCurrent ? 'text-primary font-semibold' : 'text-white' }}">{{ $item->workItem->title }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isCurrent)
                            <span class="text-xs text-primary font-medium">Current</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer
    let remaining = {{ $session->remainingSeconds() }};
    const timerEl = document.getElementById('timer-value');
    const timerPill = document.getElementById('timer-pill');
    
    if (remaining <= 0) {
        timerEl.textContent = '00:00';
        timerPill.classList.add('animate-pulse');
        // Auto-redirect after a moment
        setTimeout(() => {
            window.location.href = '{{ route("poker.results", $session->id) }}';
        }, 2000);
        return;
    }

    const interval = setInterval(() => {
        remaining--;
        
        if (remaining <= 0) {
            clearInterval(interval);
            timerEl.textContent = '00:00';
            timerPill.classList.add('animate-pulse');
            // Redirect to results when timer expires
            setTimeout(() => {
                window.location.href = '{{ route("poker.results", $session->id) }}';
            }, 2000);
            return;
        }
        
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        // Change color when low on time
        if (remaining <= 30) {
            timerPill.classList.add('animate-pulse');
        }
    }, 1000);
});
</script>
@endpush
