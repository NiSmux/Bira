@extends('layouts.app')

@section('title', 'Create Session – Planning Poker – Bira')

@section('content')
<div class="p-8 max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('poker.index', ['team_id' => $selectedTeamId]) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
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

    {{-- No team context guard --}}
    @if(!$selectedTeamId)
        <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm">
            <p class="font-semibold mb-1">No team selected</p>
            <p>Please open a board first, then use the Planning Poker link from the sidebar to create a session for that team.</p>
        </div>
    @else

    {{-- Form --}}
    <form action="{{ route('poker.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Hidden team_id --}}
        <input type="hidden" name="team_id" value="{{ $selectedTeamId }}">

        {{-- Team display (read-only) --}}
        @php $selectedTeam = $teams->firstWhere('id', $selectedTeamId); @endphp
        @if($selectedTeam)
            <div class="flex items-center gap-3 px-4 py-3 bg-primary/10 border border-primary/20 rounded-2xl">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="text-white font-semibold">{{ $selectedTeam->name }}</span>
            </div>
        @endif

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
                <p id="work-items-placeholder" class="text-muted-foreground/50 text-sm text-center py-6">Loading work items...</p>
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
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('work-items-list');
    const placeholder = document.getElementById('work-items-placeholder');
    const teamId = {{ $selectedTeamId ?? 'null' }};

    function loadItems(teamId) {
        if (!teamId) return;

        placeholder.textContent = 'Loading work items...';
        placeholder.classList.remove('hidden');
        container.classList.add('hidden');

        fetch(`/poker/team/${teamId}/items`)
            .then(res => res.json())
            .then(items => {
                container.innerHTML = '';

                if (items.length === 0) {
                    placeholder.textContent = 'No work items found for this team.';
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

    // Auto-load on page load since team is already known
    if (teamId) {
        loadItems(teamId);
    }
});
</script>
@endpush

