@extends('layouts.app')

@section('title', '{{ $session->title }} – Planning Poker – Bira')

@section('content')
@php
    $pokerState = 'voting';
    if (isset($showingResultsForCurrent) && $showingResultsForCurrent) {
        $pokerState = 'results';
    } elseif (isset($waitingForOthers) && $waitingForOthers) {
        $pokerState = 'waiting';
    }
@endphp
<div id="poker-container" data-state="{{ $pokerState }}" data-item-id="{{ $currentItem ? $currentItem->id : '' }}" class="p-8 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Planning Poker</h1>
            <p class="text-muted-foreground mt-1">Estimate story points with your team</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Timer or Live Mode Badge --}}
            @if(!$session->isLive())
                <div id="timer-pill" class="flex items-center gap-2 px-4 py-2 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 font-mono font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span id="timer-value">{{ gmdate('i:s', $session->remainingSeconds()) }}</span>
                </div>
            @else
                <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-sm">
                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M12 12a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                    <span>Live Mode</span>
                </div>
            @endif

            {{-- Reveal / Complete (only for creator) --}}
            @if(auth()->id() === $session->created_by)
                <form action="{{ route('poker.complete', [$session->id, 'board_id' => request('board_id'), 'team_id' => request('team_id')]) }}" method="POST" class="inline">
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

        <div class="mb-6">
            <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-3">Participants ({{ $members->count() }})</h3>
            <div class="participants-container flex items-center gap-3 flex-wrap">
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

        {{-- Waiting or Voting Cards --}}
        @if(isset($showingResultsForCurrent) && $showingResultsForCurrent)
            <div class="mb-8 p-8 bg-card border border-border-subtle rounded-2xl">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Everyone has voted!</h3>
                    <p class="text-muted-foreground">Here are the results for this task.</p>
                </div>
                
                <div class="flex justify-center gap-6 flex-wrap mb-8">
                    @foreach($members as $member)
                        @php
                            $userVote = $currentItem->votes->where('user_id', $member->id)->first();
                        @endphp
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-primary/20 text-primary flex items-center justify-center text-sm font-bold shadow-lg">
                                {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strstr($member->name, ' ') ?: '', 1, 1)) }}
                            </div>
                            <span class="text-xs text-muted-foreground">{{ Str::before($member->name, ' ') }}</span>
                            <div class="w-14 h-20 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-bold text-2xl text-white shadow-inner">
                                {{ $userVote ? ($userVote->points === null ? '?' : $userVote->points) : '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="flex flex-col items-center gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Consensus</span>
                    <span class="text-4xl font-bold text-primary">{{ $currentItem->consensusPoints() ?? '?' }}</span>
                </div>

                @if(auth()->id() === $session->created_by)
                    <div class="mt-8 flex justify-center gap-4 border-t border-white/5 pt-6">
                        <form action="{{ route('poker.restartTask', [$session->id, $currentItem->id, 'board_id' => request('board_id'), 'team_id' => request('team_id')]) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-semibold rounded-xl transition-all active:scale-[0.98]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Restart Voting
                            </button>
                        </form>
                        <form action="{{ route('poker.nextTask', [$session->id, $currentItem->id, 'board_id' => request('board_id'), 'team_id' => request('team_id')]) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-primary/20 text-lg">
                                Accept & Next Task
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-8 text-center border-t border-white/5 pt-6">
                        <p class="text-sm text-muted-foreground animate-pulse">Waiting for the session creator to advance to the next task...</p>
                    </div>
                @endif
            </div>
        @elseif(isset($waitingForOthers) && $waitingForOthers)
            <div class="mb-8 p-8 text-center bg-card border border-border-subtle rounded-2xl">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4 animate-pulse">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Waiting for other members...</h3>
                <p class="text-muted-foreground">You have cast your vote. The results will be revealed once everyone has voted.</p>
            </div>
        @else
            {{-- Voting Cards --}}
            <div class="mb-8">
                <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-4">Select Your Estimate</h3>
                <div class="grid grid-cols-5 sm:grid-cols-9 gap-3">
                    @php
                        $userVote = $currentItem->votes->where('user_id', $user->id)->first();
                    @endphp
                    @foreach($fibonacciCards as $card)
                        <form action="{{ route('poker.vote', [$session->id, $currentItem->id, 'board_id' => request('board_id'), 'team_id' => request('team_id')]) }}" method="POST">
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
    @if(!$session->isLive())
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
                    window.location.href = '{{ route("poker.results", [$session->id, "board_id" => request("board_id"), "team_id" => request("team_id")]) }}';
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
    @endif

    // Unified polling logic for live synchronization
    const container = document.getElementById('poker-container');
    const currentState = container ? container.dataset.state : null;
    const currentItemId = container ? container.dataset.itemId : null;

    if (container) {
        setInterval(() => {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => {
                    if (response.redirected && response.url.includes('/results')) {
                        window.location.href = response.url;
                        return null;
                    }
                    return response.text();
                })
                .then(html => {
                    if (!html) return;
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.getElementById('poker-container');
                    
                    if (!newContainer) return;

                    const newState = newContainer.dataset.state;
                    const newItemId = newContainer.dataset.itemId;

                    // If the phase changed OR the active item changed, we must do a clean reload to display the new UI state
                    if (newState !== currentState || newItemId !== currentItemId) {
                        window.location.reload();
                        return;
                    }

                    // Otherwise, we remain in the exact same state (e.g., waiting, or still deciding)
                    // We only need to silently update the participant badges so we can see who else just voted.
                    const newParticipants = newContainer.querySelector('.participants-container');
                    const currentParticipants = document.querySelector('.participants-container');
                    if (newParticipants && currentParticipants) {
                        currentParticipants.innerHTML = newParticipants.innerHTML;
                    }
                })
                .catch(e => console.error(e));
        }, 3000); // 3 seconds poll
    }
});
</script>
@endpush
