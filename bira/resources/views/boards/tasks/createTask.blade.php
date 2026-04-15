@extends('layouts.app')

@section('title', 'New task - ' . $board->name)

@section('content')
<div class="max-w-3xl mx-auto px-8 py-12">
    <div class="mb-8">
        <a href="{{ $redirectTo ?? route('boards.show', $board->id) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ isset($redirectTo) && str_contains($redirectTo, 'backlog') ? 'Back to backlog' : 'Back to board' }}
        </a>
        <h2 class="text-3xl font-bold tracking-tight text-white">New task</h2>
        <p class="text-muted-foreground mt-1">Create a new task for the board: <span class="text-white font-medium">{{ $board->name }}</span></p>
    </div>

    <div class="bg-card border border-border-subtle rounded-2xl p-8 shadow-sm">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('boards.tasks.store', $board->id) }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif

            <div>
                <label for="title" class="block text-sm font-semibold text-white mb-2">Task title</label>
                <input type="text" 
                       id="title"
                       name="title" 
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                       value="{{ old('title') }}" 
                       required 
                       placeholder="e.g.: Create login page">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-white mb-2">Description</label>
                <textarea id="description"
                          name="description" 
                          rows="4" 
                          class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all resize-none"
                          placeholder="Describe the task in detail...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="item_type_id" class="block text-sm font-semibold text-white mb-2">Type</label>
                    <select id="item_type_id" 
                            name="item_type_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                            required>
                        <option disabled selected>Select type...</option>
                        @foreach($itemTypes as $type)
                            <option value="{{ $type->id }}" {{ old('item_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority_id" class="block text-sm font-semibold text-white mb-2">Priority</label>
                    <select id="priority_id" 
                            name="priority_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                        <option value="">-- No priority --</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="story_points" class="block text-sm font-semibold text-white mb-2">Story Points</label>
                    <input type="number" 
                           id="story_points"
                           name="story_points" 
                           class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                           value="{{ old('story_points') }}" 
                           min="0" 
                           max="100" 
                           placeholder="e.g.: 5">
                </div>

            </div>

            {{-- Tags Section --}}
            <style>
                .tag-checkbox:checked + .tag-label {
                    opacity: 1 !important;
                    filter: none !important;
                    background-color: var(--tag-bg) !important;
                    border-color: var(--tag-color) !important;
                }
                .tag-checkbox:not(:checked) + .tag-label {
                    opacity: 0.4 !important;
                    filter: grayscale(80%) !important;
                    background-color: transparent !important;
                    border-color: rgba(255,255,255,0.1) !important;
                }
            </style>
            <div class="pt-6 border-t border-border-subtle">
                <label class="block text-sm font-semibold text-white mb-3">Tags</label>
                <div class="flex flex-wrap gap-2 mb-3 items-center" id="tags-container">
                    @foreach($board->tags as $tag)
                        <div>
                            <input type="checkbox" id="tag_{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}" class="hidden tag-checkbox" {{ (is_array(old('tags')) && in_array($tag->id, old('tags'))) ? 'checked' : '' }}>
                            <label for="tag_{{ $tag->id }}" class="tag-label px-3 py-1.5 rounded-lg text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5 hover:opacity-80 border-transparent shadow-sm"
                                   style="--tag-bg: {{ $tag->color }}1a; --tag-color: {{ $tag->color }}; color: {{ $tag->color }};">
                                <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $tag->color }}"></div>
                                {{ $tag->name }}
                            </label>
                        </div>
                    @endforeach
                    <button type="button" id="show-custom-tag-btn" onclick="toggleCustomTagForm()" class="px-3 py-1.5 rounded-lg text-xs font-bold border border-dashed border-white/20 text-muted-foreground hover:text-white hover:border-white/40 transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Custom Tag
                    </button>

                    <div id="custom-tag-form" class="hidden items-center gap-2 bg-white/5 p-1.5 rounded-lg border border-white/10">
                        <input type="text" id="new_tag_name" placeholder="Tag name" class="bg-background border border-border-subtle rounded-md px-2 py-1 text-xs text-white focus:outline-none focus:border-primary w-24">
                        <input type="color" id="new_tag_color" value="#3b82f6" class="w-6 h-6 rounded cursor-pointer border-0 p-0 bg-transparent">
                        <button type="button" onclick="saveCustomTag()" class="bg-primary hover:bg-primary/90 text-white px-3 py-1 rounded-md text-xs font-bold transition-all">Add</button>
                        <button type="button" onclick="toggleCustomTagForm()" class="text-muted-foreground hover:text-white px-2">✕</button>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-border-subtle">
                <div class="col-span-full">
                    <label class="block text-sm font-semibold text-white mb-3">Assign to</label>
                    <div class="flex gap-2 mb-3">
                        <button type="button" class="assignee-type-btn px-3 py-1.5 rounded-lg text-xs font-bold border transition-all bg-white/10 border-white/20 text-white" data-type="none">None</button>
                        <button type="button" class="assignee-type-btn px-3 py-1.5 rounded-lg text-xs font-bold border transition-all bg-white/5 border-white/10 text-muted-foreground" data-type="user">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                User
                            </span>
                        </button>
                        @if(isset($subTeams) && $subTeams->isNotEmpty())
                        <button type="button" class="assignee-type-btn px-3 py-1.5 rounded-lg text-xs font-bold border transition-all bg-white/5 border-white/10 text-muted-foreground" data-type="sub_team">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.231-1.48-.628-2.143M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.231-1.48.628-2.143M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Sub-Team
                            </span>
                        </button>
                        @endif
                    </div>

                    <input type="hidden" name="assignee_type" id="assignee_type_input" value="">

                    <div id="assign-user-select" class="hidden">
                        <select name="assignee_id" id="assignee_id"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <option value="">-- Select user --</option>
                            @if(isset($boardMembers))
                                @foreach($boardMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    @if(isset($subTeams) && $subTeams->isNotEmpty())
                    <div id="assign-subteam-select" class="hidden">
                        <select name="sub_team_id" id="sub_team_id"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all">
                            <option value="">-- Select sub-team --</option>
                            @foreach($subTeams as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->members->count() }} members)</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <div class="pt-6 border-t border-border-subtle flex items-center justify-between">
                <a href="{{ $redirectTo ?? route('boards.show', $board->id) }}" class="px-6 py-2.5 rounded-xl text-white font-medium hover:bg-white/5 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                    Create task
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeInput  = document.getElementById('assignee_type_input');
    const userDiv    = document.getElementById('assign-user-select');
    const subTeamDiv = document.getElementById('assign-subteam-select');
    const buttons    = document.querySelectorAll('.assignee-type-btn');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            buttons.forEach(b => {
                b.classList.remove('bg-white/10', 'border-white/20', 'text-white', 'bg-violet-600/20', 'border-violet-500/30', 'text-violet-400');
                b.classList.add('bg-white/5', 'border-white/10', 'text-muted-foreground');
            });
            if (type === 'sub_team') {
                btn.classList.add('bg-violet-600/20', 'border-violet-500/30', 'text-violet-400');
            } else {
                btn.classList.add('bg-white/10', 'border-white/20', 'text-white');
            }
            btn.classList.remove('bg-white/5', 'border-white/10', 'text-muted-foreground');
            
            typeInput.value = type === 'none' ? '' : type;
            if(userDiv) userDiv.classList.toggle('hidden', type !== 'user');
            if(subTeamDiv) subTeamDiv.classList.toggle('hidden', type !== 'sub_team');
        });
    });
});

function toggleCustomTagForm() {
    const btn = document.getElementById('show-custom-tag-btn');
    const form = document.getElementById('custom-tag-form');
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        form.classList.add('flex');
        btn.classList.add('hidden');
    } else {
        form.classList.add('hidden');
        form.classList.remove('flex');
        btn.classList.remove('hidden');
    }
}

function saveCustomTag() {
    const nameInput = document.getElementById('new_tag_name');
    const colorInput = document.getElementById('new_tag_color');
    const name = nameInput.value.trim();
    const color = colorInput.value;

    if (!name) return alert('Tag name cannot be empty');

    fetch(`{{ route('boards.tags.store', $board->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, color })
    }).then(res => res.json()).then(data => {
        if (data.success) {
            const tag = data.tag;
            const container = document.getElementById('tags-container');
            const btn = document.getElementById('show-custom-tag-btn');
            
            const div = document.createElement('div');
            div.innerHTML = `
                <input type="checkbox" id="tag_${tag.id}" name="tags[]" value="${tag.id}" class="hidden tag-checkbox" checked>
                <label for="tag_${tag.id}" class="tag-label px-3 py-1.5 rounded-lg text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5 hover:opacity-80 border-transparent shadow-sm"
                       style="--tag-bg: ${tag.color}1a; --tag-color: ${tag.color}; color: ${tag.color};">
                    <div class="w-2.5 h-2.5 rounded-full" style="background-color: ${tag.color}"></div>
                    ${tag.name}
                </label>
            `;
            container.insertBefore(div, btn);

            nameInput.value = '';
            toggleCustomTagForm();
        } else {
            alert('Failed to save tag');
        }
    }).catch(err => {
        console.error(err);
        alert('Error saving tag');
    });
}
</script>
@endpush
@endsection