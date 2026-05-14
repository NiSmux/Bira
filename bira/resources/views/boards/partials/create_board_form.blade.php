<div class="flex items-center justify-between mb-12">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <div>
            <h3 class="text-white font-black text-2xl tracking-tight">New Kanban Board</h3>
            <p class="text-muted-foreground text-sm font-medium">Create a new space for your team's projects</p>
        </div>
    </div>
    <button type="button" class="close-board-modal p-2 rounded-xl hover:bg-white/5 text-muted-foreground transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

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
    <form action="{{ route('boards.store') }}" method="POST" id="create-board-form-modal">
        @csrf
        
        <div class="pt-6">
            {{-- Group 1: Board Name --}}
            <div class="flex flex-col gap-3 mb-12">
                <label for="board_name_modal" class="block text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] ml-1">Board name</label>
                <input type="text" 
                       name="name" 
                       id="board_name_modal" 
                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder:text-muted-foreground/30 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-medium" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus
                       placeholder="e.g.: Marketing Campaign 2024">
            </div>

            {{-- Group 2: Team Selection --}}
            <div class="flex flex-col gap-3 mb-12 pt-4">
                <label for="team_id_modal" class="block text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] ml-1">Assign to team</label>
                <div class="relative group/select">
                    <select name="team_id" 
                            id="team_id_modal" 
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white appearance-none focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-medium cursor-pointer" 
                            required>
                        <option value="" class="bg-card">Select a team</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $preselectedTeamId) == $team->id) class="bg-card text-white">
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Group 3: Estimation Mode --}}
            <div class="flex flex-col gap-3 mb-12 pt-4">
                <label for="estimation_mode_modal" class="block text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] ml-1">Estimation Mode</label>
                <div class="relative group/select">
                    <select name="estimation_mode" 
                            id="estimation_mode_modal" 
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white appearance-none focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-medium cursor-pointer" 
                            required>
                        <option value="points" @selected(old('estimation_mode') == 'points' || !old('estimation_mode')) class="bg-card text-white">Story Points</option>
                        <option value="hours" @selected(old('estimation_mode') == 'hours') class="bg-card text-white">Estimated Hours</option>
                    </select>
                    <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div id="modal-members-section" class="hidden flex flex-col gap-4">
                <label class="block text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] ml-1">Board members & roles</label>
                <p class="text-[10px] text-muted-foreground mb-1 uppercase tracking-tighter ml-1">Include team members and assign their project roles.</p>
                
                <div id="modal-members-loading" class="hidden p-6 text-center">
                    <div class="inline-flex items-center gap-2 text-muted-foreground text-sm">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Loading members...
                    </div>
                </div>

                <div id="modal-members-list" class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar"></div>
            </div>
        </div>

        {{-- Massive spacer to ensure the footer is pushed down --}}
        <div class="h-16"></div>

        <div class="pt-10 border-t border-white/5 flex gap-4">
            <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white px-8 py-4 rounded-2xl font-black text-lg transition-all shadow-xl shadow-primary/25 active:scale-95 leading-none">
                Create Board
            </button>
            <button type="button" class="close-board-modal flex-1 px-8 py-4 rounded-2xl text-white font-bold border border-white/10 hover:bg-white/5 transition-all active:scale-95">
                Cancel
            </button>
        </div>
    </form>

    <script>
        (function() {
            const container = document.getElementById('create-board-form-modal');
            const teamSelect = container.querySelector('#team_id_modal');
            const membersSection = container.querySelector('#modal-members-section');
            const membersList = container.querySelector('#modal-members-list');
            const membersLoading = container.querySelector('#modal-members-loading');
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
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(response => response.json())
                .then(members => {
                    membersLoading.classList.add('hidden');
                    members.forEach((member, index) => {
                        const isCurrentUser = member.id === currentUserId;
                        const div = document.createElement('div');
                        div.className = 'flex items-center gap-4 p-3 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-all';
                        div.innerHTML = `
                            <label class="flex items-center gap-3 flex-1 cursor-pointer min-w-0">
                                <input type="checkbox" class="member-checkbox w-4 h-4 rounded border-white/20 bg-white/5 text-primary focus:ring-primary/50 accent-[var(--primary)]"
                                       data-user-id="${member.id}" ${isCurrentUser ? 'checked disabled' : 'checked'}>
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary shrink-0">
                                        ${member.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-semibold text-white block truncate">${member.name}</span>
                                        <span class="text-[10px] text-muted-foreground block truncate">${member.email}</span>
                                    </div>
                                </div>
                            </label>
                            <select class="role-select bg-sidebar border border-white/10 rounded-lg px-2 py-1 text-[10px] font-bold text-white focus:outline-none focus:ring-1 focus:ring-primary/50 transition-all appearance-none cursor-pointer"
                                    data-user-id="${member.id}">
                                ${Object.entries(roles).map(([value, label]) => 
                                    `<option value="${value}" ${isCurrentUser && value === 'product_owner' ? 'selected' : (!isCurrentUser && value === 'fe_dev' ? 'selected' : '')}>${label}</option>`
                                ).join('')}
                            </select>
                        `;
                        membersList.appendChild(div);
                        addHiddenInputs(member.id, isCurrentUser ? 'product_owner' : 'fe_dev', index);
                    });
                    bindCheckboxEvents();
                });
            }

            function addHiddenInputs(userId, role, index) {
                container.querySelectorAll(`input[data-member-hidden="${userId}"]`).forEach(el => el.remove());
                const uI = document.createElement('input'); uI.type='hidden'; uI.name=`members[${index}][user_id]`; uI.value=userId; uI.setAttribute('data-member-hidden', userId);
                const rI = document.createElement('input'); rI.type='hidden'; rI.name=`members[${index}][role]`; rI.value=role; rI.setAttribute('data-member-hidden', userId);
                rI.classList.add('role-hidden-input'); rI.setAttribute('data-user-id', userId);
                container.appendChild(uI); container.appendChild(rI);
            }

            function rebuildHiddenInputs() {
                container.querySelectorAll('input[data-member-hidden]').forEach(el => el.remove());
                let idx = 0;
                membersList.querySelectorAll('.member-checkbox').forEach(cb => {
                    const uId = cb.getAttribute('data-user-id');
                    if (cb.checked || parseInt(uId) === currentUserId) {
                        const rs = membersList.querySelector(`.role-select[data-user-id="${uId}"]`);
                        addHiddenInputs(uId, rs ? rs.value : 'fe_dev', idx++);
                    }
                });
            }

            function bindCheckboxEvents() {
                membersList.querySelectorAll('.member-checkbox').forEach(cb => cb.addEventListener('change', rebuildHiddenInputs));
                membersList.querySelectorAll('.role-select').forEach(sel => sel.addEventListener('change', () => {
                    const uId = sel.getAttribute('data-user-id');
                    const hr = container.querySelector(`.role-hidden-input[data-user-id="${uId}"]`);
                    if (hr) hr.value = sel.value;
                }));
            }

            teamSelect.addEventListener('change', () => fetchTeamMembers(teamSelect.value));
            if (teamSelect.value) fetchTeamMembers(teamSelect.value);

            // Modal Close Helper
            const closeModal = () => {
                const modal = document.getElementById('board-create-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            document.querySelectorAll('.close-board-modal').forEach(btn => btn.addEventListener('click', closeModal));
        })();
    </script>
@endif
