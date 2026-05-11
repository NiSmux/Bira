@extends('layouts.app')

@section('title', 'Create new board')

@section('content')
<div class="max-w-3xl mx-auto px-8 py-12">
    <div class="mb-10">
        <a href="{{ route('boards.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to list
        </a>
        <h2 class="text-4xl font-black tracking-tight text-white mb-2">New Kanban board</h2>
        <p class="text-muted-foreground text-lg">Create a new space to manage your team's projects.</p>
    </div>

    <div class="bg-card border border-border-subtle rounded-3xl p-8 lg:p-10 shadow-2xl overflow-hidden relative group">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
        
        @if($teams->isEmpty())
            <div class="text-center py-10 relative z-10">
                <div class="w-20 h-20 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 mx-auto mb-6 border border-amber-500/20 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">Team required</h3>
                <p class="text-muted-foreground text-lg mb-8 max-w-md mx-auto">A board cannot exist without a team. Create your first team to start creating projects.</p>
                <a href="{{ route('teams.create') }}" class="inline-flex items-center gap-3 bg-primary hover:bg-primary/90 text-white px-8 py-3.5 rounded-2xl font-bold transition-all shadow-xl shadow-primary/30 active:scale-95 leading-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create team
                </a>
            </div>
        @else
            <form action="{{ route('boards.store') }}" method="POST" class="space-y-8 relative z-10" id="create-board-form">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-muted-foreground uppercase tracking-widest mb-3">Board name</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="w-full bg-background border @error('name') border-red-500/50 @else border-border-subtle @enderror rounded-2xl px-6 py-4 text-white placeholder:text-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-xl font-medium" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus
                               placeholder="e.g.: Marketing Campaign 2024">
                        @error('name')
                            <p class="mt-2 text-sm text-red-400 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="team_id" class="block text-sm font-bold text-muted-foreground uppercase tracking-widest mb-3">Assign to team</label>
                        <div class="relative group/select">
                            <select name="team_id" 
                                    id="team_id" 
                                    class="w-full bg-background border @error('team_id') border-red-500/50 @else border-border-subtle @enderror rounded-2xl px-6 py-4 text-white appearance-none focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-medium cursor-pointer" 
                                    required>
                                <option value="" class="bg-card">Select a team</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('team_id', $preselectedTeamId) == $team->id) class="bg-card">
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('team_id')
                            <p class="mt-2 text-sm text-red-400 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="estimation_mode" class="block text-sm font-bold text-muted-foreground uppercase tracking-widest mb-3">Estimation Mode</label>
                        <div class="relative group/select">
                            <select name="estimation_mode" 
                                    id="estimation_mode" 
                                    class="w-full bg-background border border-border-subtle rounded-2xl px-6 py-4 text-white appearance-none focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-medium cursor-pointer" 
                                    required>
                                <option value="points" @selected(old('estimation_mode') == 'points' || !old('estimation_mode')) class="bg-card text-white">Story Points</option>
                                <option value="hours" @selected(old('estimation_mode') == 'hours') class="bg-card text-white">Estimated Hours</option>
                            </select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Members Section --}}
                    <div id="members-section" class="hidden">
                        <label class="block text-sm font-bold text-muted-foreground uppercase tracking-widest mb-3">Board members & roles</label>
                        <p class="text-xs text-muted-foreground mb-4">Select which team members to include and assign their roles.</p>
                        
                        <div id="members-loading" class="hidden p-6 text-center">
                            <div class="inline-flex items-center gap-2 text-muted-foreground text-sm">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Loading members...
                            </div>
                        </div>

                        <div id="members-list" class="space-y-3"></div>

                        @error('members')
                            <p class="mt-2 text-sm text-red-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-8 border-t border-white/5 flex flex-col sm:flex-row items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto bg-primary hover:bg-primary/90 text-white px-10 py-4 rounded-2xl font-black text-lg transition-all shadow-xl shadow-primary/25 active:scale-95 leading-none">
                        Create board
                    </button>
                    <a href="{{ route('boards.index') }}" class="w-full sm:w-auto text-center px-10 py-4 rounded-2xl text-white font-bold hover:bg-white/5 transition-all active:scale-95">
                        Cancel
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const teamSelect = document.getElementById('team_id');
    const membersSection = document.getElementById('members-section');
    const membersList = document.getElementById('members-list');
    const membersLoading = document.getElementById('members-loading');

    const roles = @json($roles);
    const currentUserId = {{ Auth::user()->id }};

    function fetchTeamMembers(teamId) {
        if (!teamId) {
            membersSection.classList.add('hidden');
            membersList.innerHTML = '';
            return;
        }

        membersSection.classList.remove('hidden');
        membersLoading.classList.remove('hidden');
        membersList.innerHTML = '';

        fetch(`/api/teams/${teamId}/members`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(members => {
            membersLoading.classList.add('hidden');
            
            members.forEach((member, index) => {
                const isCurrentUser = member.id === currentUserId;
                const div = document.createElement('div');
                div.className = 'flex items-center gap-4 p-4 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 transition-all';
                
                div.innerHTML = `
                    <label class="flex items-center gap-3 flex-1 cursor-pointer min-w-0">
                        <input type="checkbox" 
                               class="member-checkbox w-4 h-4 rounded border-white/20 bg-white/5 text-primary focus:ring-primary/50 accent-[var(--primary)] shrink-0"
                               data-user-id="${member.id}"
                               ${isCurrentUser ? 'checked disabled' : 'checked'}
                        >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary shrink-0">
                                ${member.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-medium text-white block truncate">${member.name}</span>
                                <span class="text-xs text-muted-foreground block truncate">${member.email}</span>
                            </div>
                        </div>
                    </label>
                    <select class="role-select bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all appearance-none cursor-pointer shrink-0"
                            data-user-id="${member.id}">
                        ${Object.entries(roles).map(([value, label]) => 
                            `<option value="${value}" ${isCurrentUser && value === 'product_owner' ? 'selected' : (!isCurrentUser && value === 'fe_dev' ? 'selected' : '')}>${label}</option>`
                        ).join('')}
                    </select>
                `;

                membersList.appendChild(div);

                // Hidden inputs for the form
                if (isCurrentUser) {
                    addHiddenInputs(member.id, 'product_owner', index);
                } else {
                    addHiddenInputs(member.id, 'fe_dev', index);
                }
            });

            // Re-bind checkbox events
            bindCheckboxEvents();
        })
        .catch(error => {
            membersLoading.classList.add('hidden');
            membersList.innerHTML = '<p class="text-red-400 text-sm p-4">Error loading team members.</p>';
        });
    }

    function addHiddenInputs(userId, role, index) {
        const container = document.getElementById('create-board-form');
        
        // Remove existing hidden inputs for this user
        container.querySelectorAll(`input[data-member-hidden="${userId}"]`).forEach(el => el.remove());

        const userInput = document.createElement('input');
        userInput.type = 'hidden';
        userInput.name = `members[${index}][user_id]`;
        userInput.value = userId;
        userInput.setAttribute('data-member-hidden', userId);

        const roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = `members[${index}][role]`;
        roleInput.value = role;
        roleInput.setAttribute('data-member-hidden', userId);
        roleInput.classList.add('role-hidden-input');
        roleInput.setAttribute('data-user-id', userId);

        container.appendChild(userInput);
        container.appendChild(roleInput);
    }

    function removeHiddenInputs(userId) {
        const container = document.getElementById('create-board-form');
        container.querySelectorAll(`input[data-member-hidden="${userId}"]`).forEach(el => el.remove());
    }

    function rebuildHiddenInputs() {
        const container = document.getElementById('create-board-form');
        // Remove all existing member hidden inputs
        container.querySelectorAll('input[data-member-hidden]').forEach(el => el.remove());

        // Rebuild from checked checkboxes
        let index = 0;
        membersList.querySelectorAll('.member-checkbox').forEach(cb => {
            const userId = cb.getAttribute('data-user-id');
            const isCurrentUser = parseInt(userId) === currentUserId;
            
            if (cb.checked || isCurrentUser) {
                const roleSelect = membersList.querySelector(`.role-select[data-user-id="${userId}"]`);
                const role = roleSelect ? roleSelect.value : 'fe_dev';
                addHiddenInputs(userId, role, index);
                index++;
            }
        });
    }

    function bindCheckboxEvents() {
        membersList.querySelectorAll('.member-checkbox').forEach(cb => {
            cb.addEventListener('change', () => rebuildHiddenInputs());
        });

        membersList.querySelectorAll('.role-select').forEach(select => {
            select.addEventListener('change', () => {
                const userId = select.getAttribute('data-user-id');
                const hiddenRole = document.querySelector(`.role-hidden-input[data-user-id="${userId}"]`);
                if (hiddenRole) {
                    hiddenRole.value = select.value;
                }
            });
        });
    }

    // Listen for team selection
    teamSelect.addEventListener('change', () => {
        fetchTeamMembers(teamSelect.value);
    });

    // If preselected (from team page "+" button)
    if (teamSelect.value) {
        fetchTeamMembers(teamSelect.value);
    }
});
</script>
@endpush