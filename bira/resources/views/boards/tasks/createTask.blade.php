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

        @include('boards.tasks._task_form')
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