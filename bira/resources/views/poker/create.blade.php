@extends('layouts.app')

@section('title', 'Create Session – Planning Poker – Bira')

@section('content')
<div class="p-8 max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('poker.index', array_filter(['team_id' => $selectedTeamId, 'board_id' => $boardId ?? null])) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Sessions
        </a>
        <h1 class="text-3xl font-bold text-white tracking-tight">Create Session</h1>
        <p class="text-muted-foreground mt-1">Set up a new Planning Poker session for your team</p>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('poker.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Hidden team_id and board_id --}}
        <input type="hidden" name="team_id" id="team_id" value="{{ $selectedTeamId }}">
        <input type="hidden" name="board_id" id="board_id" value="{{ $boardId }}">

        {{-- Context Display --}}
        <div class="space-y-4 px-4 py-5 bg-primary/10 border border-primary/20 rounded-2xl">
            @php 
                $selectedTeam = $teams->firstWhere('id', $selectedTeamId);
                $selectedBoard = $selectedTeam ? $selectedTeam->boards->firstWhere('id', $boardId) : null;
            @endphp
            
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-primary uppercase tracking-widest">Team</span>
                    <span class="text-white font-semibold">{{ $selectedTeam->name ?? 'Unknown Team' }}</span>
                </div>
            </div>

            @if($selectedBoard)
            <div class="pt-4 border-t border-primary/10 flex items-center gap-3">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-primary uppercase tracking-widest">Board</span>
                    <span class="text-white font-semibold">{{ $selectedBoard->name }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Session Title --}}
        <div class="space-y-2">
            <label for="title" class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider pl-1">Session Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                   class="block w-full px-4 py-3 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-muted-foreground/30 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                   placeholder="e.g. Sprint 5 Estimation">
        </div>

        {{-- Time Limit --}}
        <div class="space-y-2">
            <label for="time_limit" class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider pl-1">Time Limit (minutes)</label>
            <input type="number" id="time_limit" name="time_limit" value="{{ old('time_limit', 5) }}" min="1" max="120" required
                   class="block w-full px-4 py-3 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-muted-foreground/30 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all">
        </div>

        {{-- Work Items --}}
        <div class="space-y-2">
            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider pl-1">Work Items to Estimate</label>
            <div id="work-items-container" class="bg-white/5 border border-white/10 rounded-2xl p-4 min-h-[120px]">
                <p id="work-items-placeholder" class="text-muted-foreground/50 text-sm text-center py-6">Select a board to load items.</p>
                <div id="work-items-list" class="space-y-2 hidden"></div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-2xl shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-primary/20 transition-all active:scale-[0.98]">
                Create Session
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const boardId = document.getElementById('board_id').value;
    const container = document.getElementById('work-items-list');
    const placeholder = document.getElementById('work-items-placeholder');

    function loadItems(boardId) {
        if (!boardId) {
            placeholder.textContent = 'No board selected. Please create a session from a board page.';
            placeholder.classList.remove('hidden');
            container.classList.add('hidden');
            return;
        }

        placeholder.textContent = 'Loading work items...';
        placeholder.classList.remove('hidden');
        container.classList.add('hidden');

        fetch(`/poker/board/${boardId}/items`)
            .then(res => res.json())
            .then(items => {
                container.innerHTML = '';

                if (items.length === 0) {
                    placeholder.textContent = 'No work items found for this board.';
                    placeholder.classList.remove('hidden');
                    container.classList.add('hidden');
                    return;
                }

                placeholder.classList.add('hidden');
                container.classList.remove('hidden');

                items.forEach(item => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition-colors cursor-pointer';
                    label.innerHTML = `
                        <input type="checkbox" name="work_items[]" value="${item.id}"
                               class="w-4 h-4 rounded bg-white/5 border-white/20 text-primary focus:ring-primary/20 focus:ring-offset-0">
                        <div class="flex-1 min-w-0">
                            <span class="text-white text-sm font-medium">${item.title}</span>
                            ${item.story_points ? `<span class="ml-2 text-xs text-muted-foreground">(${item.story_points} pts)</span>` : ''}
                        </div>
                    `;
                    container.appendChild(label);
                });
            })
            .catch(() => {
                placeholder.textContent = 'Error loading items. Please try again.';
                placeholder.classList.remove('hidden');
                container.classList.add('hidden');
            });
    }

    // Initial load
    loadItems(boardId);
});
</script>
@endpush

