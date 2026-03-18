@extends('layouts.app')

@section('title', 'Create Session – Planning Poker – Bira')

@section('content')
<div class="p-8 max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('poker.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
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

        {{-- Session Title --}}
        <div class="space-y-2">
            <label for="title" class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider pl-1">Session Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                   class="block w-full px-4 py-3 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-muted-foreground/30 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                   placeholder="e.g. Sprint 5 Estimation">
        </div>

        {{-- Team Selection --}}
        <div class="space-y-2">
            <label for="team_id" class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider pl-1">Team</label>
            <select id="team_id" name="team_id" required
                    class="block w-full px-4 py-3 bg-white/5 border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all appearance-none">
                <option value="" disabled {{ !isset($selectedTeamId) ? 'selected' : '' }} class="bg-card text-muted-foreground">Select a team...</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" class="bg-card text-white" {{ (old('team_id') == $team->id || (isset($selectedTeamId) && $selectedTeamId == $team->id)) ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
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
                <p id="work-items-placeholder" class="text-muted-foreground/50 text-sm text-center py-6">Select a team first to load work items...</p>
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
    const teamSelect = document.getElementById('team_id');
    const container = document.getElementById('work-items-list');
    const placeholder = document.getElementById('work-items-placeholder');

    teamSelect.addEventListener('change', function() {
        const teamId = this.value;
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
    });

    // Trigger on page load if team was already selected (old value)
    if (teamSelect.value) {
        teamSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
