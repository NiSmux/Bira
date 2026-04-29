<form id="create-task-form" action="{{ route('boards.tasks.store', $board->id) }}" method="POST" class="space-y-6">
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
                <option disabled {{ old('item_type_id') === null && !isset($defaultItemTypeId) ? 'selected' : '' }}>Select type...</option>
                @foreach($itemTypes as $type)
                    <option value="{{ $type->id }}" {{ (old('item_type_id') !== null ? old('item_type_id') == $type->id : (isset($defaultItemTypeId) && $defaultItemTypeId == $type->id)) ? 'selected' : '' }}>
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
            @if($board->estimation_mode === 'hours')
                <label for="estimated_hours" class="block text-sm font-semibold text-white mb-2">Estimated Hours</label>
                <input type="number" 
                       step="0.5"
                       id="estimated_hours"
                       name="estimated_hours" 
                       min="0" max="1000"
                       value="{{ old('estimated_hours') }}" 
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all font-medium">
            @else
                <label for="story_points" class="block text-sm font-semibold text-white mb-2">Story Points</label>
                <input type="number" 
                       id="story_points"
                       name="story_points" 
                       min="0" max="100"
                       value="{{ old('story_points') }}" 
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all font-medium">
            @endif
        </div>
    </div>

    {{-- Tags Section --}}
    <div class="pt-6 border-t border-border-subtle">
        <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-semibold text-white">Tags</label>
            <div class="flex items-center gap-2">
                <button type="button" id="tag-manage-edit-btn" onclick="toggleTagManagement('edit')" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-white transition-all border border-transparent hover:border-white/10" title="Edit tags">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </button>
                <button type="button" id="tag-manage-delete-btn" onclick="toggleTagManagement('delete')" class="p-1.5 rounded-lg hover:bg-white/5 text-muted-foreground hover:text-red-400 transition-all border border-transparent hover:border-white/10" title="Delete tags">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 mb-3 items-center" id="tags-container">
            @foreach($board->tags as $tag)
                <div class="tag-wrapper" data-tag-id="{{ $tag->id }}" data-tag-name="{{ $tag->name }}" data-tag-color="{{ $tag->color }}" data-board-id="{{ $board->id }}">
                    <input type="checkbox" id="tag_{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}" class="hidden tag-checkbox" {{ (is_array(old('tags')) && in_array($tag->id, old('tags'))) ? 'checked' : '' }}>
                    <label for="tag_{{ $tag->id }}" class="tag-label px-3 py-1.5 rounded-lg text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5 hover:opacity-80 border-transparent shadow-sm"
                           style="--tag-bg: {{ $tag->color }}1a; --tag-color: {{ $tag->color }}; color: {{ $tag->color }};">
                        <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $tag->color }}"></div>
                        <span class="tag-name">{{ $tag->name }}</span>
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
        <button type="button" class="cancel-modal-btn px-6 py-2.5 rounded-xl text-white font-medium hover:bg-white/5 transition-colors">
            Cancel
        </button>
        <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
            Create task
        </button>
    </div>
</form>
