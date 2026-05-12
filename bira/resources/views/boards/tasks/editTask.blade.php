@extends('layouts.app')

@section('title', 'Edit task - ' . $task->title)

@section('content')
<div class="max-w-3xl mx-auto px-8 py-12">
    <div class="mb-8">
        <a href="{{ $redirectTo ?? route('boards.show', $board->id) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to {{ isset($redirectTo) && str_contains($redirectTo, 'backlog') ? 'backlog' : 'board' }}
        </a>
        <h2 class="text-3xl font-bold tracking-tight text-white">Edit task</h2>
        <p class="text-muted-foreground mt-1 text-sm">Editing task: <span class="text-white font-medium">{{ $task->title }}</span></p>
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

        <form action="{{ route('boards.tasks.update', [$board->id, $task->id]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            @if(isset($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif

            <div>
                <label for="title" class="block text-sm font-semibold text-white mb-2">Title</label>
                <input type="text" 
                       id="title"
                       name="title" 
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                       value="{{ old('title', $task->title) }}" 
                       required>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-white mb-2">Description</label>
                <textarea id="description"
                          name="description" 
                          rows="4" 
                          class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all resize-none">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="status_id" class="block text-sm font-semibold text-white mb-2">Status (Column)</label>
                    <select id="status_id" 
                            name="status_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                            required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="item_type_id" class="block text-sm font-semibold text-white mb-2">Type</label>
                    <select id="item_type_id"
                            name="item_type_id"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                            required>
                        @foreach($itemTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-color="{{ $type->color ?? '' }}"
                                    data-icon="{{ $type->icon ?? '' }}"
                                    {{ old('item_type_id', $task->item_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->icon ? $type->icon . ' ' : '' }}{{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="priority_id" class="block text-sm font-semibold text-white mb-2">Priority</label>
                    <select id="priority_id" 
                            name="priority_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                        <option value="">-- No priority --</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id', $task->priority_id) == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($board->estimation_mode === 'hours')
                <div>
                    <label for="estimated_hours" class="block text-sm font-semibold text-white mb-2">Estimated Hours</label>
                    <input type="number"
                           step="0.5"
                           id="estimated_hours"
                           name="estimated_hours"
                           min="0" max="1000"
                           value="{{ old('estimated_hours', $task->estimated_hours) }}"
                           class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all font-medium">
                </div>
                @else
                <div>
                    <label for="story_points" class="block text-sm font-semibold text-white mb-2">Story Points</label>
                    <input type="number"
                           id="story_points"
                           name="story_points"
                           min="0" max="100"
                           value="{{ old('story_points', $task->story_points) }}"
                           data-sp-rate="{{ $board->sp_to_hours_rate }}"
                           class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all font-medium">
                </div>
                @if($board->sp_to_hours_rate)
                </div>{{-- close grid row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="estimated_hours" class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                        Estimated Hours
                        <span class="text-[10px] font-normal text-primary bg-primary/10 px-1.5 py-0.5 rounded">auto · 1SP={{ $board->sp_to_hours_rate }}h</span>
                    </label>
                    <input type="number"
                           step="0.5"
                           id="estimated_hours"
                           name="estimated_hours"
                           min="0" max="1000"
                           value="{{ old('estimated_hours', $task->estimated_hours) }}"
                           placeholder="Auto-calculated"
                           class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all font-medium">
                    <p class="text-[10px] text-muted-foreground mt-1">Override auto-calculation by typing a value.</p>
                </div>
                @else
                <input type="hidden" name="estimated_hours" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                @endif
                @endif
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
                
                /* Management Modes */
                .tag-container-managing-delete .tag-label {
                    cursor: pointer !important;
                }
                .tag-container-managing-delete .tag-label:hover {
                    border-color: #ef4444 !important;
                    background-color: rgba(239, 68, 68, 0.1) !important;
                }
                .tag-container-managing-delete .tag-to-delete .tag-label {
                    border-color: #ef4444 !important;
                    background-color: rgba(239, 68, 68, 0.2) !important;
                    opacity: 1 !important;
                    filter: none !important;
                    box-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
                }
                .tag-container-managing-edit .tag-label:hover {
                    border-color: #3b82f6 !important;
                    background-color: rgba(59, 130, 246, 0.1) !important;
                }
            </style>
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
                    @php $taskTags = $task->tags->pluck('id')->toArray(); @endphp
                    @foreach($board->tags as $tag)
                        <div class="tag-wrapper" data-tag-id="{{ $tag->id }}" data-tag-name="{{ $tag->name }}" data-tag-color="{{ $tag->color }}">
                            <input type="checkbox" id="tag_{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}" class="hidden tag-checkbox" {{ (is_array(old('tags')) && in_array($tag->id, old('tags'))) || (!old('tags') && in_array($tag->id, $taskTags)) ? 'checked' : '' }}>
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

            {{-- Assignee Section --}}
            @php
                $currentAssigneeType = 'none';
                if ($task->assignee_id) $currentAssigneeType = 'user';
                elseif ($task->sub_team_id) $currentAssigneeType = 'sub_team';
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-border-subtle">
                <div class="col-span-full">
                    <label class="block text-sm font-semibold text-white mb-3">Assign to</label>
                    <div class="flex gap-2 mb-3">
                        <button type="button" class="assignee-type-btn px-3 py-1.5 rounded-lg text-xs font-bold border transition-all {{ $currentAssigneeType === 'none' ? 'bg-white/10 border-white/20 text-white' : 'bg-white/5 border-white/10 text-muted-foreground' }}" data-type="none">None</button>
                        <button type="button" class="assignee-type-btn px-3 py-1.5 rounded-lg text-xs font-bold border transition-all {{ $currentAssigneeType === 'user' ? 'bg-white/10 border-white/20 text-white' : 'bg-white/5 border-white/10 text-muted-foreground' }}" data-type="user">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                User
                            </span>
                        </button>
                        @if(isset($subTeams) && $subTeams->isNotEmpty())
                        <button type="button" class="assignee-type-btn px-3 py-1.5 rounded-lg text-xs font-bold border transition-all {{ $currentAssigneeType === 'sub_team' ? 'bg-violet-600/20 border-violet-500/30 text-violet-400' : 'bg-white/5 border-white/10 text-muted-foreground' }}" data-type="sub_team">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.231-1.48-.628-2.143M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.231-1.48.628-2.143M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Sub-Team
                            </span>
                        </button>
                        @endif
                    </div>

                    <input type="hidden" name="assignee_type" id="assignee_type_input" value="{{ $currentAssigneeType === 'none' ? '' : $currentAssigneeType }}">

                    <div id="assign-user-select" class="{{ $currentAssigneeType === 'user' ? '' : 'hidden' }}">
                        <select name="assignee_id" id="assignee_id"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <option value="">-- Select user --</option>
                            @if(isset($boardMembers))
                                @foreach($boardMembers as $member)
                                    <option value="{{ $member->id }}" {{ $task->assignee_id == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    @if(isset($subTeams) && $subTeams->isNotEmpty())
                    <div id="assign-subteam-select" class="{{ $currentAssigneeType === 'sub_team' ? '' : 'hidden' }}">
                        <select name="sub_team_id" id="sub_team_id"
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all">
                            <option value="">-- Select sub-team --</option>
                            @foreach($subTeams as $st)
                                <option value="{{ $st->id }}" {{ $task->sub_team_id == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->members->count() }} members)</option>
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
                    Update task
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Tag Modal --}}
<div id="tag-edit-modal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display:none;">
    <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-white font-bold text-lg mb-5">Edit Tag</h3>
        <div class="space-y-4">
            <input type="hidden" id="edit_tag_id">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Tag name</label>
                <input type="text" id="edit_tag_name" required maxlength="80"
                    class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Color</label>
                <input type="color" id="edit_tag_color" required
                    class="w-full h-12 border border-border-subtle p-1 bg-background rounded-xl cursor-pointer">
            </div>
            <div class="flex gap-3 justify-end mt-6">
                <button type="button" onclick="closeTagEditModal()" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
                <button type="button" onclick="updateTag()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg transition-colors">Save updates</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// SP → hours auto-converter
(function() {
    const spInput = document.getElementById('story_points');
    const hrsInput = document.getElementById('estimated_hours');
    if (!spInput || !hrsInput) return;
    const rate = parseFloat(spInput.dataset.spRate);
    if (!rate) return;
    let userEditedHours = !!hrsInput.value;
    spInput.addEventListener('input', function() {
        if (userEditedHours) return;
        const sp = parseFloat(this.value);
        hrsInput.value = (!isNaN(sp) && sp >= 0) ? Math.round(sp * rate * 4) / 4 : '';
    });
    hrsInput.addEventListener('input', function() {
        userEditedHours = this.value !== '';
    });
    hrsInput.addEventListener('blur', function() {
        if (!this.value) userEditedHours = false;
    });
})();

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

let tagManagementMode = 'none'; // 'none', 'edit', 'delete'
let selectedTagsForDeletion = new Set();

function toggleTagManagement(mode) {
    const container = document.getElementById('tags-container');
    const deleteBtn = document.getElementById('tag-manage-delete-btn');
    const editBtn = document.getElementById('tag-manage-edit-btn');
    
    // If clicking the SAME mode that is already active
    if (tagManagementMode === mode) {
        if (mode === 'delete' && selectedTagsForDeletion.size > 0) {
            confirmBatchDelete();
            return;
        }
        exitManagementMode();
        return;
    }

    // Enter NEW mode
    exitManagementMode();
    tagManagementMode = mode;
    
    if (mode === 'delete') {
        container.classList.add('tag-container-managing-delete');
        deleteBtn.classList.add('bg-red-500/20', 'border-red-500/50', 'text-red-400');
        deleteBtn.classList.remove('text-muted-foreground');
        // Prevent default checkbox behavior
        document.querySelectorAll('.tag-wrapper label').forEach(label => label.addEventListener('click', handleTagManagementClick, { capture: true }));
    } else if (mode === 'edit') {
        container.classList.add('tag-container-managing-edit');
        editBtn.classList.add('bg-primary/20', 'border-primary/50', 'text-white');
        editBtn.classList.remove('text-muted-foreground');
        document.querySelectorAll('.tag-wrapper label').forEach(label => label.addEventListener('click', handleTagManagementClick, { capture: true }));
    }
}

function exitManagementMode() {
    const container = document.getElementById('tags-container');
    const deleteBtn = document.getElementById('tag-manage-delete-btn');
    const editBtn = document.getElementById('tag-manage-edit-btn');

    container.classList.remove('tag-container-managing-delete', 'tag-container-managing-edit');
    deleteBtn.classList.remove('bg-red-500/20', 'border-red-500/50', 'text-red-400');
    deleteBtn.classList.add('text-muted-foreground');
    editBtn.classList.remove('bg-primary/20', 'border-primary/50', 'text-white');
    editBtn.classList.add('text-muted-foreground');
    
    document.querySelectorAll('.tag-wrapper label').forEach(label => label.removeEventListener('click', handleTagManagementClick, { capture: true }));
    document.querySelectorAll('.tag-wrapper').forEach(w => w.classList.remove('tag-to-delete'));
    
    selectedTagsForDeletion.clear();
    tagManagementMode = 'none';
}

function handleTagManagementClick(e) {
    if (tagManagementMode === 'none') return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const wrapper = e.currentTarget.closest('.tag-wrapper');
    const tagId = wrapper.dataset.tagId;
    
    if (tagManagementMode === 'delete') {
        if (selectedTagsForDeletion.has(tagId)) {
            selectedTagsForDeletion.delete(tagId);
            wrapper.classList.remove('tag-to-delete');
        } else {
            selectedTagsForDeletion.add(tagId);
            wrapper.classList.add('tag-to-delete');
        }
    } else if (tagManagementMode === 'edit') {
        openTagEditModal(wrapper.dataset);
    }
}

function openTagEditModal(data) {
    document.getElementById('edit_tag_id').value = data.tagId;
    document.getElementById('edit_tag_name').value = data.tagName;
    document.getElementById('edit_tag_color').value = data.tagColor;
    document.getElementById('tag-edit-modal').style.display = 'flex';
}

function closeTagEditModal() {
    document.getElementById('tag-edit-modal').style.display = 'none';
    exitManagementMode();
}

function updateTag() {
    const id = document.getElementById('edit_tag_id').value;
    const name = document.getElementById('edit_tag_name').value.trim();
    const color = document.getElementById('edit_tag_color').value;
    
    if (!name) return alert('Tag name cannot be empty');

    fetch(`/boards/{{ $board->id }}/tags/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, color })
    }).then(res => res.json()).then(data => {
        if (data.success) {
            // Update UI
            const wrapper = document.querySelector(`.tag-wrapper[data-tag-id="${id}"]`);
            if (wrapper) {
                wrapper.dataset.tagName = name;
                wrapper.dataset.tagColor = color;
                const label = wrapper.querySelector('.tag-label');
                label.style.setProperty('--tag-color', color);
                label.style.setProperty('--tag-bg', color + '1a');
                label.style.color = color;
                label.querySelector('.w-2.5').style.backgroundColor = color;
                label.querySelector('.tag-name').textContent = name;
            }
            closeTagEditModal();
        } else {
            alert('Failed to update tag');
        }
    });
}

function confirmBatchDelete() {
    if (!confirm(`Delete ${selectedTagsForDeletion.size} tags? This will remove them from all tasks.`)) return;

    fetch(`/boards/{{ $board->id }}/tags/batch-delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ tag_ids: Array.from(selectedTagsForDeletion) })
    }).then(res => res.json()).then(data => {
        if (data.success) {
            selectedTagsForDeletion.forEach(id => {
                document.querySelector(`.tag-wrapper[data-tag-id="${id}"]`)?.remove();
            });
            exitManagementMode();
        } else {
            alert('Failed to delete tags');
        }
    });
}

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
            div.className = 'tag-wrapper';
            div.dataset.tagId = tag.id;
            div.dataset.tagName = tag.name;
            div.dataset.tagColor = tag.color;
            div.innerHTML = `
                <input type="checkbox" id="tag_${tag.id}" name="tags[]" value="${tag.id}" class="hidden tag-checkbox" checked>
                <label for="tag_${tag.id}" class="tag-label px-3 py-1.5 rounded-lg text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5 hover:opacity-80 border-transparent shadow-sm"
                       style="--tag-bg: ${tag.color}1a; --tag-color: ${tag.color}; color: ${tag.color};">
                    <div class="w-2.5 h-2.5 rounded-full" style="background-color: ${tag.color}"></div>
                    <span class="tag-name">${tag.name}</span>
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