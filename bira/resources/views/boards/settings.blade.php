@extends('layouts.app')

@section('title', $board->name . ' - Settings')

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $board->name }}</h2>
                <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider border border-primary/20">Settings</span>
            </div>
            <p class="text-muted-foreground">{{ $board->team->name }} &middot; Manage board members and roles</p>
        </div>
        <a href="{{ route('boards.show', $board->id) }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-all border border-white/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to board
        </a>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400">
            @foreach($errors->all() as $error)
                <p class="text-sm">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Sidebar: Add Member --}}
        {{-- Sidebar: Settings --}}
        <div class="space-y-8">
            {{-- Estimation Mode --}}
            @if($isBoardAdmin)
                <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Estimation Mode
                    </h3>
                    <form action="{{ route('boards.update_mode', $board->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <select name="estimation_mode" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all appearance-none cursor-pointer" required>
                                <option value="points" @selected($board->estimation_mode === 'points')>Story Points</option>
                                <option value="hours" @selected($board->estimation_mode === 'hours')>Estimated Hours</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-white/5 hover:bg-white/10 border border-white/10 text-white py-2.5 rounded-xl font-bold transition-all active:scale-[0.98]">
                            Save Metric
                        </button>
                    </form>
                </div>
            @endif

            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Add member to board
                </h3>

                @if($availableMembers->isEmpty())
                    <div class="p-4 rounded-xl bg-white/5 border border-dashed border-white/10 text-center">
                        <p class="text-xs text-muted-foreground italic">All team members are already on this board.</p>
                    </div>
                @else
                    <form action="{{ route('boards.members.store', $board->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Team member</label>
                            <select name="user_id" id="user_id" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all appearance-none" required>
                                <option value="">Select member</option>
                                @foreach($availableMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="role" class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Role</label>
                            <select name="role" id="role" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all appearance-none" required>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-xl font-bold transition-all active:scale-[0.98]">
                            Add to board
                        </button>
                    </form>
                @endif
            </div>

            {{-- Role Legend --}}
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Permission levels
                </h3>
                <div class="space-y-3 text-xs">
                    <div class="flex items-start gap-2">
                        <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-500 font-bold uppercase text-[9px] shrink-0 mt-0.5">Admin</span>
                        <span class="text-muted-foreground">Product Owner, Tech Lead, Team Lead — full board control</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 font-bold uppercase text-[9px] shrink-0 mt-0.5">Member</span>
                        <span class="text-muted-foreground">FE/BE Dev, Fullstack, QA — create & edit tasks</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="px-1.5 py-0.5 rounded bg-gray-500/10 text-gray-400 font-bold uppercase text-[9px] shrink-0 mt-0.5">Viewer</span>
                        <span class="text-muted-foreground">Read-only access to board</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content: Member Table --}}
        <div class="lg:col-span-2">
            <div class="bg-card border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between bg-white/5">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground">Board members</h3>
                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider">{{ $board->members->count() }} members</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5 border-b border-border-subtle">
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Name</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Email</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Role</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($board->members as $member)
                                @php
                                    $permLevel = app(App\Http\Controllers\BoardController::class)->getPermissionLevel($member->pivot->role);
                                    $roleStyle = match($permLevel) {
                                        'admin'  => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                        'member' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'viewer' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                        default  => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                    };
                                @endphp
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strstr($member->name, ' ') ?: '', 1, 1)) }}
                                            </div>
                                            <span class="text-sm font-medium text-white">{{ $member->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-muted-foreground">{{ $member->email }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('boards.members.updateRole', [$board->id, $member->id]) }}" method="POST" class="inline-role-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="bg-transparent border border-transparent hover:border-white/10 rounded-lg px-2 py-1 text-xs font-bold uppercase tracking-tighter cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/50 {{ $roleStyle }}" onchange="this.form.submit()">
                                                @foreach($roles as $value => $label)
                                                    <option value="{{ $value }}" @selected($member->pivot->role === $value) class="bg-card text-white">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($member->id !== Auth::user()->id)
                                            <form action="{{ route('boards.members.destroy', [$board->id, $member->id]) }}" method="POST" onsubmit="return confirm('Remove this member from the board?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         SUB-TEAMS SECTION (admin only)
    ═══════════════════════════════════════════════════════ --}}
    @if($isBoardAdmin)
    <div class="mt-10 border-t border-white/5 pt-10">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold tracking-tight text-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.231-1.48-.628-2.143M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.231-1.48.628-2.143M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                Sub-Teams
            </h3>
            <span class="px-2 py-0.5 rounded-lg bg-violet-500/10 text-violet-400 text-[10px] font-bold uppercase tracking-wider border border-violet-500/20">{{ $board->subTeams->count() }} teams</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Sidebar: Create sub-team --}}
            <div class="space-y-6">
                <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        New Sub-Team
                    </h4>
                    <form action="{{ route('boards.sub-teams.store', $board->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="sub_team_name" class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Team name</label>
                            <input type="text" name="name" id="sub_team_name" required maxlength="120"
                                class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all"
                                placeholder="e.g. Frontend Team">
                        </div>
                        <button type="submit" class="w-full bg-violet-600 hover:bg-violet-700 text-white py-2.5 rounded-xl font-bold transition-all active:scale-[0.98]">
                            Create Sub-Team
                        </button>
                    </form>
                </div>
            </div>

            {{-- Main: Sub-teams list --}}
            <div class="lg:col-span-2 space-y-4">
                @forelse($board->subTeams as $subTeam)
                <div class="bg-card border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
                    {{-- Sub-team header --}}
                    <div class="px-5 py-4 bg-white/[0.03] border-b border-border-subtle flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-violet-500/15 flex items-center justify-center text-[10px] font-bold text-violet-400">
                                {{ strtoupper(substr($subTeam->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ $subTeam->name }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ $subTeam->members->count() }} members</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- Edit button --}}
                            <button
                                class="st-edit-btn p-1.5 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors"
                                data-id="{{ $subTeam->id }}"
                                data-name="{{ $subTeam->name }}"
                                title="Rename sub-team">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            {{-- Delete button --}}
                            <form action="{{ route('boards.sub-teams.destroy', [$board->id, $subTeam->id]) }}" method="POST"
                                onsubmit="return confirm('Delete sub-team {{ addslashes($subTeam->name) }}? Tasks assigned to it will be unassigned.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-500/10 text-muted-foreground hover:text-red-400 transition-colors" title="Delete sub-team">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Members --}}
                    <div class="px-5 py-3">
                        @if($subTeam->members->isEmpty())
                            <p class="text-xs text-muted-foreground italic py-2">No members yet.</p>
                        @else
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($subTeam->members as $stMember)
                            <div class="flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-full px-2.5 py-1 text-xs font-medium text-white group/chip">
                                <div class="w-4 h-4 rounded-full bg-violet-500/20 flex items-center justify-center text-[9px] font-bold text-violet-400">
                                    {{ strtoupper(substr($stMember->name, 0, 1)) }}
                                </div>
                                <span>{{ $stMember->name }}</span>
                                <form action="{{ route('boards.sub-teams.members.destroy', [$board->id, $subTeam->id, $stMember->id]) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-muted-foreground/40 hover:text-red-400 transition-colors ml-0.5" title="Remove from sub-team">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Add member form --}}
                        @php
                            $stMemberIds = $subTeam->members->pluck('id')->toArray();
                            $availableForSt = $board->members->filter(fn($m) => !in_array($m->id, $stMemberIds));
                        @endphp
                        @if($availableForSt->isNotEmpty())
                        <form action="{{ route('boards.sub-teams.members.store', [$board->id, $subTeam->id]) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <select name="user_id" required class="flex-1 bg-background border border-border-subtle rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 appearance-none">
                                <option value="">Add member...</option>
                                @foreach($availableForSt as $availMember)
                                    <option value="{{ $availMember->id }}">{{ $availMember->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-violet-600/80 hover:bg-violet-600 text-white text-xs font-bold rounded-lg transition-colors">
                                Add
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-10 flex flex-col items-center justify-center text-muted-foreground text-sm opacity-50 bg-white/[0.01] border border-dashed border-white/10 rounded-2xl">
                    No sub-teams yet. Create one to organize your board members into groups.
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Edit Sub-Team Modal --}}
<div id="st-edit-modal" class="fixed inset-0 z-50 items-center justify-center" style="display:none;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="st-edit-backdrop"></div>
    <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-sm mx-4 shadow-2xl">
        <h3 class="text-white font-bold text-lg mb-5">Rename Sub-Team</h3>
        <form id="st-edit-form" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">New name</label>
                <input type="text" name="name" id="st-edit-name" required maxlength="120"
                    class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 text-sm">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" id="st-edit-cancel" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold rounded-lg transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal    = document.getElementById('st-edit-modal');
    const form     = document.getElementById('st-edit-form');
    const nameInp  = document.getElementById('st-edit-name');
    const cancel   = document.getElementById('st-edit-cancel');
    const backdrop = document.getElementById('st-edit-backdrop');

    document.querySelectorAll('.st-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id   = btn.dataset.id;
            const name = btn.dataset.name;
            nameInp.value = name;
            form.action = `/boards/{{ $board->id }}/sub-teams/${id}`;
            modal.style.display = 'flex';
            nameInp.focus();
        });
    });

    const closeModal = () => { modal.style.display = 'none'; };
    cancel?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);
});
</script>
@endpush
@endsection
