@extends('layouts.app')

@section('title', ($isOwnProfile ? 'My Profile' : $user->name . "'s Profile") . ($board ? ' - Board: ' . $board->name : ($team ? ' - Team: ' . $team->name : '')) . ' – Bira')

@section('hide_sidebar', true)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white">{{ $isOwnProfile ? 'My Profile' : $user->name . "'s Profile" }}</h2>
            @if($board)
                <p class="text-sm text-muted-foreground mt-1">Viewing activity for board: <span class="text-primary font-semibold">{{ $board->name }}</span></p>
            @elseif($team)
                <p class="text-sm text-muted-foreground mt-1">Viewing activity for team: <span class="text-primary font-semibold">{{ $team->name }}</span></p>
            @endif
        </div>
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-all border border-white/10">
            <x-lucide-arrow-left class="w-4 h-4" />
            Back
        </a>
    </div>
    
    @if(session('success'))
        <div class="alert-container mb-8 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <div class="flex items-center gap-3">
                <x-lucide-check-circle class="w-5 h-5" />
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <!-- Profile Header -->
    <div class="mb-10 relative overflow-hidden rounded-3xl bg-linear-to-br from-primary/20 to-purple-500/10 border border-white/5 p-8 sm:p-12">
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-8">
            <div class="w-32 h-32 rounded-full bg-primary/20 backdrop-blur-md border-4 border-white/10 flex items-center justify-center text-4xl font-bold text-white shadow-2xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: '', 1, 1)) }}
            </div>
            <div class="text-center sm:text-left">
                <h1 class="text-4xl font-extrabold tracking-tight text-white mb-2">{{ $user->name }}</h1>
                <p class="text-lg text-muted-foreground mb-4">{{ $user->email }}</p>
                <div class="flex flex-wrap justify-center sm:justify-start gap-3">
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-sm font-medium text-white">
                        <x-lucide-shield-check class="w-4 h-4 text-primary" />
                        {{ $role ? $role->name : 'User' }}
                    </span>
                    @if($board)
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-primary/10 border border-primary/20 text-sm font-medium text-primary">
                            <x-lucide-layout-dashboard class="w-4 h-4" />
                            Board stats only
                        </span>
                    @elseif($team)
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-primary/10 border border-primary/20 text-sm font-medium text-primary">
                            <x-lucide-users class="w-4 h-4" />
                            Team stats only
                        </span>
                    @endif
                    @if(!$user->is_active)
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-red-500/10 border border-red-500/20 text-sm font-medium text-red-400">
                            <x-lucide-circle-x class="w-4 h-4" />
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <!-- Decorative blob -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/20 rounded-full blur-3xl opacity-50"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/30 transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-1">Created tasks</p>
                    <h3 class="text-3xl font-bold text-white">{{ $sukurtuUzduociu }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                    <x-lucide-plus-circle class="w-6 h-6" />
                </div>
            </div>
        </div>
        <div class="bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/30 transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-1">Assigned tasks</p>
                    <h3 class="text-3xl font-bold text-white">{{ $priskirtuUzduociu }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 group-hover:scale-110 transition-transform">
                    <x-lucide-user class="w-6 h-6" />
                </div>
            </div>
        </div>
        <div class="bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/30 transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-1">Completed tasks</p>
                    <h3 class="text-3xl font-bold text-white">{{ $atliktaUzduociu }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                    <x-lucide-check-circle class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Columns (Actions & Teams) -->
        <div class="space-y-8">
            <!-- Account Details -->
            <div class="bg-card border border-border-subtle rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-border-subtle bg-white/2">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Account details</h4>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <x-lucide-user class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Name</p>
                            <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <x-lucide-mail class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Email</p>
                            <p class="text-sm font-medium text-white truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <x-lucide-shield-check class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">System role</p>
                            <p class="text-sm font-medium text-white">{{ $role ? $role->name : '—' }}</p>
                        </div>
                    </div>

                    @if($boardRole)
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <x-lucide-award class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Board role</p>
                            <p class="text-sm font-medium text-white">{{ $boardRole }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <x-lucide-zap class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Account status</p>
                            <p class="text-sm font-medium {{ $user->is_active ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2 border-t border-white/5">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <x-lucide-calendar class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Registration date</p>
                            <p class="text-sm font-medium text-white">{{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teams -->
            <div class="bg-card border border-border-subtle rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-border-subtle bg-white/2">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $isOwnProfile ? 'My teams' : 'Teams' }}</h4>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($teams as $team)
                        @if($isOwnProfile)
                            <a href="{{ route('teams.show', $team->id) }}" class="flex items-start gap-3 p-3 rounded-xl bg-white/2 border border-white/5 hover:border-primary/30 transition-colors group/team">
                        @else
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white/2 border border-white/5 group/team">
                        @endif
                            <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary {{ $isOwnProfile ? 'group-hover/team:scale-110' : '' }} transition-transform">
                                <x-lucide-users class="w-4 h-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-white truncate {{ $isOwnProfile ? 'group-hover/team:text-primary' : '' }} transition-colors">{{ $team->name }}</p>
                                @if($team->role_in_team)
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $team->role_in_team }}</p>
                                @endif
                            </div>
                            @if($isOwnProfile)
                                <div class="self-center opacity-0 group-hover/team:opacity-100 transition-opacity">
                                    <x-lucide-chevron-right class="w-4 h-4 text-primary" />
                                </div>
                            @endif
                        @if($isOwnProfile)
                            </a>
                        @else
                            </div>
                        @endif
                    @empty
                        <p class="text-xs text-muted-foreground text-center py-4 italic">{{ $isOwnProfile ? 'You are' : $user->name . ' is' }} not a member of any team.</p>
                    @endforelse
                </div>
            </div>

            @if($isOwnProfile)
            <!-- Actions -->
            <div class="bg-white/2 border border-border-subtle rounded-3xl p-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground mb-6 text-center">Profile settings</h4>
                <div class="space-y-3">
                    <a href="{{ route('profilis.redaguoti') }}" class="flex items-center justify-center gap-2 w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-primary/20">
                        <x-lucide-square-pen class="w-5 h-5" />
                        Edit profile
                    </a>
                    <a href="{{ route('profilis.slaptazodis') }}" class="flex items-center justify-center gap-2 w-full bg-white/5 hover:bg-white/10 text-white font-bold py-3 px-4 rounded-xl transition-all border border-white/10">
                        <x-lucide-key-round class="w-5 h-5" />
                        Change password
                    </a>
                    <div class="pt-4 mt-4 border-t border-white/5">
                        <form action="{{ route('profilis.trinti') }}" method="POST" onsubmit="return confirm('WARNING! Are you sure you want to completely delete your profile? All your data will be removed.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center justify-center gap-2 w-full bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold py-3 px-4 rounded-xl transition-all border border-red-500/20 group">
                                <x-lucide-trash-2 class="w-5 h-5 group-hover:shake" />
                                Delete account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Tasks Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-card border border-border-subtle rounded-2xl flex flex-col h-full shadow-sm">
                <div class="px-8 py-6 border-b border-border-subtle flex items-center justify-between bg-white/2">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Recent actions (tasks)</h4>
                    <span class="px-2.5 py-1 rounded-full bg-white/5 text-[10px] font-bold text-muted-foreground uppercase">
                        {{ $paskutinesUzduotys->count() }} actions
                    </span>
                </div>

                <div class="p-4 space-y-3 flex-1">
                    @forelse($paskutinesUzduotys as $task)
                        <div class="group bg-white/2 border border-white/5 rounded-2xl p-4 hover:border-primary/50 transition-all">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    @php
                                        $priorityStyles = match(mb_strtolower($task->prioritetas ?? '')) {
                                            'urgent'    => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'high'   => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                            'medium' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'low'     => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            default     => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                        };
                                    @endphp
                                    <div class="w-2 h-2 rounded-full {{ explode(' ', $priorityStyles)[0] }}" title="Prioritetas: {{ $task->prioritetas }}"></div>
                                    <div class="min-w-0">
                                        <h5 class="text-white font-semibold truncate">{{ $task->title }}</h5>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ $task->tipas }}</span>
                                            @if($task->story_points)
                                                <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 text-[10px] font-bold">
                                                    {{ $task->story_points }} SP
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-muted-foreground italic">
                                                {{ \Carbon\Carbon::parse($task->updated_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $task->is_done ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-muted-foreground' }}">
                                        @if($task->is_done)
                                            <x-lucide-check class="w-3 h-3" />
                                        @else
                                            <x-lucide-clock class="w-4 h-4" />
                                        @endif
                                        {{ $task->statusas }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center py-20 text-center">
                            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-6 border border-dashed border-white/10">
                                <x-lucide-inbox class="w-10 h-10 text-muted-foreground" />
                            </div>
                            <p class="text-muted-foreground font-medium">There are no tasks yet.</p>
                        </div>
                    @endforelse
                </div>
                
                <div class="p-6 border-t border-border-subtle bg-white/1">
                    <a href="{{ route('boards.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-white transition-colors">
                        View all boards
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }
    .group-hover\:shake:hover {
        animation: shake 0.5s ease-in-out infinite;
    }
</style>
@endsection
