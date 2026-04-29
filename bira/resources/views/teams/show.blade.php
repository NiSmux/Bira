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
                                <option value="{{ $type->id }}" {{ $team->default_item_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }} (custom)</option>
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
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-white/5 border border-white/5">
                            <span class="text-sm text-white">{{ $type->name }}</span>
                            <form action="{{ route('teams.item-types.destroy', [$team->id, $type->id]) }}" method="POST" onsubmit="return confirm('Remove this type?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors">
                                    <x-lucide-x class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Add custom type --}}
                <form action="{{ route('teams.item-types.store', $team->id) }}" method="POST">
                    @csrf
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Add custom type</label>
                    <div class="flex gap-2">
                        <input type="text" name="name" placeholder="e.g. Epic" maxlength="80" required
                               class="flex-1 bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <button type="submit" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition-all border border-white/10">
                            <x-lucide-plus class="w-4 h-4" />
                        </button>
                    </div>
                </form>
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
                                        <a href="{{ route('profilis.rodyti', $member->id) }}" class="flex items-center gap-3 group/member">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary group-hover/member:bg-primary/20 transition-colors">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(strstr($member->name, ' ') ?: '', 1, 1)) }}
                                            </div>
                                            <span class="text-sm font-medium text-white group-hover/member:text-primary transition-colors">{{ $member->name }}</span>
                                        </a>
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
@endsection