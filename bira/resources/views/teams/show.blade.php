@extends('layouts.app')

@section('title', $team->name . ' - Team')

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $team->name }}</h2>
                <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider border border-primary/20">Team</span>
            </div>
            <p class="text-muted-foreground">{{ $team->description ?: 'No description' }}</p>
        </div>
        <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-all border border-white/10">
            <x-lucide-arrow-left class="w-4 h-4" />
            Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar: Add Member & Boards List -->
        <div class="space-y-8">
        <!-- Add Member Form (owner only) -->
            @if($isOwner)
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                    <x-lucide-user-plus class="w-4 h-4" />
                    Add member
                </h3>
                
                @if($availableUsers->isEmpty())
                    <div class="p-4 rounded-xl bg-white/5 border border-dashed border-white/10 text-center">
                        <p class="text-xs text-muted-foreground italic">No more members can be added.</p>
                    </div>
                @else
                    <form action="{{ route('teams.members.store', $team->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">User</label>
                            <select name="user_id" id="user_id" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all appearance-none" required>
                                <option value="">Select user</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-xl font-bold transition-all active:scale-[0.98]">
                            Add to team
                        </button>
                    </form>
                @endif
            </div>
            @endif

            @if($isOwner)
            <!-- Task Types Settings -->
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                    <x-lucide-tag class="w-4 h-4" />
                    Task types
                </h3>

                {{-- Default type --}}
                <form action="{{ route('teams.default-type.update', $team->id) }}" method="POST" class="mb-6">
                    @csrf
                    @method('PATCH')
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Default type</label>
                    <div class="flex gap-2">
                        <select name="default_item_type_id" class="flex-1 bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <option value="">-- None --</option>
                            @foreach($globalItemTypes as $type)
                                <option value="{{ $type->id }}" {{ $team->default_item_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                            @foreach($team->itemTypes as $type)
                                <option value="{{ $type->id }}" {{ $team->default_item_type_id == $type->id ? 'selected' : '' }}>{{ $type->icon ? $type->icon . ' ' : '' }}{{ $type->name }} (custom)</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all">Save</button>
                    </div>
                </form>

                {{-- Custom types list --}}
                @if($team->itemTypes->isNotEmpty())
                <div class="mb-4 space-y-1.5">
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Custom types</label>
                    @foreach($team->itemTypes as $type)
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-white/5 border border-white/5 group">
                            <div class="flex items-center gap-2 min-w-0">
                                @if($type->color)
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $type->color }}"></span>
                                @endif
                                @if($type->icon)
                                    <span class="text-base leading-none">{{ $type->icon }}</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm text-white font-medium truncate">{{ $type->name }}</p>
                                    @if($type->description)
                                        <p class="text-[10px] text-muted-foreground truncate">{{ $type->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                <button type="button"
                                    class="type-edit-btn p-1.5 rounded hover:bg-white/10 text-muted-foreground hover:text-white transition-colors"
                                    data-id="{{ $type->id }}"
                                    data-name="{{ $type->name }}"
                                    data-icon="{{ $type->icon }}"
                                    data-color="{{ $type->color ?? '#6366f1' }}"
                                    data-description="{{ $type->description }}"
                                    title="Edit type">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form action="{{ route('teams.item-types.destroy', [$team->id, $type->id]) }}" method="POST" onsubmit="return confirm('Remove this type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded hover:bg-red-500/10 text-muted-foreground hover:text-red-400 transition-colors" title="Delete type">
                                        <x-lucide-x class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Add custom type --}}
                <details class="group/add" id="add-type-details">
                    <summary class="block text-[10px] font-bold text-muted-foreground uppercase mb-2 cursor-pointer hover:text-white transition-colors list-none flex items-center gap-1.5">
                        <svg class="w-3 h-3 transition-transform group-open/add:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Add custom type
                    </summary>
                    <form action="{{ route('teams.item-types.store', $team->id) }}" method="POST" class="mt-3 space-y-3">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="name" placeholder="e.g. Epic" maxlength="80" required
                                   class="flex-1 bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-muted-foreground mb-1">Icon (emoji)</label>
                                <input type="text" name="icon" placeholder="🐛" maxlength="10"
                                       class="w-full bg-background border border-border-subtle rounded-lg px-3 py-1.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] text-muted-foreground mb-1">Color</label>
                                <input type="color" name="color" value="#6366f1"
                                       class="w-full h-[34px] bg-background border border-border-subtle rounded-lg p-1 cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] text-muted-foreground mb-1">Description (optional)</label>
                            <input type="text" name="description" placeholder="Short description..." maxlength="255"
                                   class="w-full bg-background border border-border-subtle rounded-lg px-3 py-1.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition-all border border-white/10">
                            Add type
                        </button>
                    </form>
                </details>
            </div>
            @endif

            <!-- Team Boards -->
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                        <x-lucide-layout-dashboard class="w-4 h-4" />
                        Team boards
                    </h3>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('backlog.index', ['team_id' => $team->id]) }}" class="text-xs font-bold text-primary hover:text-primary-light transition-colors uppercase tracking-tight">All boards backlogs</a>
                        @if($isOwner)
                            <a href="{{ route('boards.create', ['team_id' => $team->id]) }}" class="p-1.5 rounded-lg bg-primary/10 hover:bg-primary/20 text-primary transition-all" title="Create new board">
                                <x-lucide-plus class="w-4 h-4" />
                            </a>
                        @endif
                    </div>
                </div>
                <div class="space-y-2">
                    @forelse($team->boards as $teamBoard)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 hover:border-primary/30 transition-all group">
                            <span class="text-sm font-medium text-white">{{ $teamBoard->name }}</span>
                            <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if(isset($isOwner) && $isOwner)
                                    <a href="{{ route('boards.settings', $teamBoard->id) }}" class="text-muted-foreground hover:text-white transition-colors" title="Board settings">
                                        <x-lucide-settings class="w-4 h-4" />
                                    </a>
                                    <form action="{{ route('boards.destroy', $teamBoard->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this board?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-500 transition-colors" title="Delete board">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('boards.show', $teamBoard->id) }}" class="text-primary hover:text-primary-light transition-colors">
                                    <x-lucide-chevron-right class="w-5 h-5" />
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-muted-foreground italic text-center py-4">This team doesn't have boards yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Content: Member Table -->
        <div class="lg:col-span-2">
            <div class="bg-card border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between bg-white/5">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground">Team members</h3>
                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider">{{ $team->members->count() }} members</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5 border-b border-border-subtle">
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Name</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Email</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Role</th>
                                @if($isOwner)
                                    <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase text-right">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($team->members as $member)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        @if($isOwner)
                                            <a href="{{ route('profilis.rodyti', ['id' => $member->id, 'team_id' => $team->id]) }}" class="flex items-center gap-3 group/member" title="View profile">
                                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary group-hover/member:bg-primary/20 transition-colors">
                                                    {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strstr($member->name, ' ') ?: '', 1, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-white group-hover/member:text-primary transition-colors">{{ $member->name }}</span>
                                            </a>
                                        @else
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                                    {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strstr($member->name, ' ') ?: '', 1, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-white">{{ $member->name }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-muted-foreground">{{ $member->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-tighter {{ $member->pivot->role_in_team === 'owner' ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                                            {{ $member->pivot->role_in_team }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($isOwner && $member->pivot->role_in_team !== 'owner')
                                            <form action="{{ route('teams.members.destroy', [$team->id, $member->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this member?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors">
                                                    <x-lucide-trash-2 class="w-5 h-5" />
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
</div>
{{-- Edit Item Type Modal --}}
<div id="type-edit-modal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display:none;">
    <div class="relative bg-[#1a1a2e] border border-white/10 rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="text-white font-bold text-lg mb-5">Edit Task Type</h3>
        <form id="type-edit-form" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Name</label>
                <input type="text" name="name" id="type-edit-name" required maxlength="80"
                    class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Icon (emoji)</label>
                    <input type="text" name="icon" id="type-edit-icon" maxlength="10" placeholder="e.g. 🐛"
                        class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Color</label>
                    <input type="color" name="color" id="type-edit-color"
                        class="w-full h-[42px] bg-background border border-border-subtle rounded-xl p-1 cursor-pointer">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Description</label>
                <input type="text" name="description" id="type-edit-description" maxlength="255" placeholder="Short description..."
                    class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm">
            </div>
            <div class="flex gap-3 justify-end mt-6">
                <button type="button" id="type-edit-cancel" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm font-medium rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg transition-colors">Save changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal   = document.getElementById('type-edit-modal');
    const form    = document.getElementById('type-edit-form');
    const cancel  = document.getElementById('type-edit-cancel');

    document.querySelectorAll('.type-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('type-edit-name').value        = btn.dataset.name;
            document.getElementById('type-edit-icon').value        = btn.dataset.icon || '';
            document.getElementById('type-edit-color').value       = btn.dataset.color || '#6366f1';
            document.getElementById('type-edit-description').value = btn.dataset.description || '';
            form.action = `/teams/{{ $team->id }}/item-types/${btn.dataset.id}`;
            modal.style.display = 'flex';
        });
    });

    cancel?.addEventListener('click', () => { modal.style.display = 'none'; });
    modal?.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
});
</script>
@endpush
@endsection