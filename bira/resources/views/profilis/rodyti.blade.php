@extends('layouts.app')

@section('title', 'My Profile – Bira')

@section('hide_sidebar', true)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    @if(session('success'))
        <div class="alert-container mb-8 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        {{ $role ? $role->name : 'User' }}
                    </span>
                    @if(!$user->is_active)
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-red-500/10 border border-red-500/20 text-sm font-medium text-red-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Name</p>
                            <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">Email</p>
                            <p class="text-sm font-medium text-white truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-muted-foreground">System role</p>
                            <p class="text-sm font-medium text-white">{{ $role ? $role->name : '—' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-muted-foreground shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
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
                    <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">My teams</h4>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($teams as $team)
                        <a href="{{ route('teams.show', $team->id) }}" class="flex items-start gap-3 p-3 rounded-xl bg-white/2 border border-white/5 hover:border-primary/30 transition-colors group/team">
                            <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary group-hover/team:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-white truncate group-hover/team:text-primary transition-colors">{{ $team->name }}</p>
                                @if($team->role_in_team)
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $team->role_in_team }}</p>
                                @endif
                            </div>
                            <div class="self-center opacity-0 group-hover/team:opacity-100 transition-opacity">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-muted-foreground text-center py-4 italic">You are not a member of any team.</p>
                    @endforelse
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white/2 border border-border-subtle rounded-3xl p-6">
                <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground mb-6 text-center">Profile settings</h4>
                <div class="space-y-3">
                    <a href="{{ route('profilis.redaguoti') }}" class="flex items-center justify-center gap-2 w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-primary/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit profile
                    </a>
                    <a href="{{ route('profilis.slaptazodis') }}" class="flex items-center justify-center gap-2 w-full bg-white/5 hover:bg-white/10 text-white font-bold py-3 px-4 rounded-xl transition-all border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 11-7.743-5.743L11 3l-2 2H5a2 2 0 00-2 2v10a2 2 0 002 2h2v2l2-2h2a2 2 0 002-2v-7a2 2 0 012-2h2a2 2 0 012 2v3m2 4l-2 2m2-2l2 2m-2-2l2-2m-2 2l-2-2"></path></svg>
                        Change password
                    </a>
                    <div class="pt-4 mt-4 border-t border-white/5">
                        <form action="{{ route('profilis.trinti') }}" method="POST" onsubmit="return confirm('WARNING! Are you sure you want to completely delete your profile? All your data will be removed.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center justify-center gap-2 w-full bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold py-3 px-4 rounded-xl transition-all border border-red-500/20 group">
                                <svg class="w-5 h-5 group-hover:shake" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @endif
                                        {{ $task->statusas }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center py-20 text-center">
                            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-6 border border-dashed border-white/10">
                                <svg class="w-10 h-10 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <p class="text-muted-foreground font-medium">There are no tasks yet.</p>
                        </div>
                    @endforelse
                </div>
                
                <div class="p-6 border-t border-border-subtle bg-white/1">
                    <a href="{{ route('boards.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-white transition-colors">
                        View all boards
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
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
