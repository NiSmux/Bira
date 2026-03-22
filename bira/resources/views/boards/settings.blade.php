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
        <div class="space-y-8">
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
</div>
@endsection
