@extends('layouts.app')

@section('title', 'Dashboard – Bira')

@section('content')
@if(!auth()->check())
    <!-- Guest Welcome Layout -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="bg-card border border-border-subtle rounded-3xl p-12 text-center shadow-2xl relative overflow-hidden">
            <!-- Decorative background element -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <h1 class="text-5xl font-extrabold tracking-tight text-white mb-6">
                    Welcome to <span class="text-primary">Bira</span>
                </h1>
                
                <p class="text-xl text-muted-foreground mb-10">
                    The platform for efficient project management and team collaboration.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 px-8 rounded-2xl transition-all shadow-lg shadow-primary/20">
                        Log In
                    </a>
                    <a href="{{ route('registracija.forma') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-8 rounded-2xl transition-all border border-white/10">
                        Get Started for Free
                    </a>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Authenticated Dashboard -->
    <div class="p-4 sm:p-6 space-y-6 animate-fade-in max-w-[1400px] mx-auto">
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Tasks -->
            <div class="bg-card border border-border-subtle rounded-lg p-3 shadow-sm relative overflow-hidden group flex flex-col justify-between">
                <div class="absolute -right-4 -top-4 w-12 h-12 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-1 relative z-10">
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">My Active Tasks</h3>
                    <div class="p-1 bg-blue-500/10 rounded text-blue-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="text-xl font-bold text-white leading-none mb-1">{{ $totalTasks }}</div>
                    <p class="text-[9px] text-muted-foreground"><span class="text-green-400 font-medium">{{ $totalDoneTasks }}</span> completed</p>
                </div>
            </div>

            <!-- Story Points & Hours -->
            <div class="bg-card border border-border-subtle rounded-lg p-3 shadow-sm relative overflow-hidden group flex flex-col justify-between">
                <div class="absolute -right-4 -top-4 w-12 h-12 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-1 relative z-10">
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Work in Sprint</h3>
                    <div class="flex gap-1.5">
                        <div class="p-1 bg-purple-500/10 rounded text-purple-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    </div>
                </div>
                <div class="relative z-10 flex gap-4">
                    <div class="flex-1">
                        <div class="text-lg font-bold text-white leading-none mb-1">{{ $storyPointsDone }} <span class="text-[10px] text-muted-foreground font-normal">/ {{ $storyPointsDone + $storyPointsLeft }} pt</span></div>
                        <div class="w-full bg-border-subtle rounded-full h-1">
                            @php $spPercent = ($storyPointsDone + $storyPointsLeft) > 0 ? ($storyPointsDone / ($storyPointsDone + $storyPointsLeft)) * 100 : 0; @endphp
                            <div class="bg-purple-500 h-1 rounded-full" style="width: {{ $spPercent }}%"></div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-lg font-bold text-white leading-none mb-1">{{ $hoursDone }} <span class="text-[10px] text-muted-foreground font-normal">/ {{ $hoursDone + $hoursLeft }} h</span></div>
                        <div class="w-full bg-border-subtle rounded-full h-1">
                            @php $hoursPercent = ($hoursDone + $hoursLeft) > 0 ? ($hoursDone / ($hoursDone + $hoursLeft)) * 100 : 0; @endphp
                            <div class="bg-orange-500 h-1 rounded-full" style="width: {{ $hoursPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Sprints -->
            <div class="bg-card border border-border-subtle rounded-lg p-3 shadow-sm relative overflow-hidden group flex flex-col justify-between">
                <div class="absolute -right-4 -top-4 w-12 h-12 bg-pink-500/10 rounded-full blur-xl group-hover:bg-pink-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-1 relative z-10">
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Active Sprints</h3>
                    <div class="p-1 bg-pink-500/10 rounded text-pink-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="text-xl font-bold text-white leading-none mb-1">{{ $activeSprintsCount }}</div>
                    <p class="text-[9px] text-muted-foreground">in your boards</p>
                </div>
            </div>

            <!-- Notifications Badge -->
            <div class="bg-card border border-border-subtle rounded-lg p-3 shadow-sm relative overflow-hidden group flex flex-col justify-between">
                <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="flex items-center justify-between mb-1 relative z-10">
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Unread</h3>
                    <div class="p-1 bg-emerald-500/10 rounded text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="text-xl font-bold text-white leading-none mb-1">{{ $notifications->where('is_read', 0)->count() }}</div>
                    <p class="text-[9px] text-muted-foreground">out of {{ $notifications->count() }} recent</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Boards and Recent Activity -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Quick Access Boards -->
                <div class="bg-card border border-border-subtle rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Recent Boards
                        </h2>
                        <a href="{{ route('boards.index') }}" class="text-xs text-primary hover:text-primary/80 transition-colors">View all</a>
                    </div>
                    
                    @if($recentBoards->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($recentBoards as $board)
                                <a href="{{ route('boards.show', $board->id) }}" class="group block p-3 rounded-lg border border-border-subtle bg-background/50 hover:bg-background transition-colors hover:border-primary/30 relative overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary transform origin-left scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-semibold text-sm text-white group-hover:text-primary transition-colors">{{ $board->name }}</h3>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-[10px] text-muted-foreground">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            {{ $board->items_count ?? 0 }} items
                                        </span>
                                        @if($board->last_accessed_at)
                                            <span>Accessed {{ \Carbon\Carbon::parse($board->last_accessed_at)->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-muted-foreground">You haven't visited any boards yet.</p>
                            <a href="{{ route('boards.index') }}" class="text-xs text-primary hover:underline mt-1 inline-block">Go to boards</a>
                        </div>
                    @endif
                </div>

                <!-- Recent Activity -->
                <div class="bg-card border border-border-subtle rounded-xl p-5">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Your Recent Activity
                    </h2>
                    
                    @if($recentTasks->count() > 0)
                        <div class="space-y-2">
                            @foreach($recentTasks as $task)
                                @php
                                    $boardId = $task->boards->first()->id ?? null;
                                @endphp
                                @if($boardId)
                                    <a href="{{ route('boards.show', $boardId) }}" class="block p-3 rounded-lg border border-border-subtle bg-background/30 hover:bg-background transition-colors group">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-0.5">
                                                @if($task->status && $task->status->is_done)
                                                    <div class="w-6 h-6 rounded-full bg-green-500/10 flex items-center justify-center text-green-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                @else
                                                    <div class="w-6 h-6 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-xs font-semibold text-white truncate group-hover:text-primary transition-colors">
                                                        {{ $task->title }}
                                                    </p>
                                                    <span class="text-[10px] text-muted-foreground whitespace-nowrap ml-2">{{ $task->updated_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1 text-[10px] text-muted-foreground">
                                                    @if($task->status)
                                                        <span class="flex items-center gap-1">
                                                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $task->status->color ?? '#6b7280' }}"></div>
                                                            {{ $task->status->name }}
                                                        </span>
                                                    @endif
                                                    @if($task->type)
                                                        <span class="px-1 py-0.5 rounded bg-border-subtle">{{ $task->type->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-sm text-muted-foreground">
                            <p>No recent activity found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Notifications -->
            <div class="bg-card border border-border-subtle rounded-xl p-5 lg:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Notifications
                    </h2>
                    @if($notifications->count() > 0)
                        <a href="{{ route('notifications.index') }}" class="text-xs text-primary hover:text-primary/80 transition-colors">See all</a>
                    @endif
                </div>

                @if($notifications->count() > 0)
                    <div class="space-y-2 overflow-y-auto pr-1" style="max-height: 500px;">
                        @foreach($notifications as $notification)
                            <div class="p-2.5 rounded-lg border {{ $notification->is_read ? 'border-border-subtle bg-background/20' : 'border-primary/30 bg-primary/5' }} flex items-start gap-2.5 transition-colors hover:bg-background/50">
                                <div class="mt-0.5">
                                    @if($notification->type === 'mention')
                                        <div class="p-1 bg-blue-500/20 text-blue-400 rounded-md"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                                    @elseif($notification->type === 'task_assigned')
                                        <div class="p-1 bg-purple-500/20 text-purple-400 rounded-md"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg></div>
                                    @else
                                        <div class="p-1 bg-gray-500/20 text-gray-400 rounded-md"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-2">
                                        <p class="text-xs font-medium {{ $notification->is_read ? 'text-gray-300' : 'text-white font-semibold' }}">
                                            {{ $notification->title }}
                                        </p>
                                        <span class="text-[9px] text-muted-foreground whitespace-nowrap mt-0.5">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-[10px] text-muted-foreground mt-0.5 line-clamp-2">{{ $notification->message }}</p>
                                    @if($notification->link)
                                        <a href="{{ $notification->link }}" class="text-[10px] text-primary hover:underline mt-1 inline-block">View details</a>
                                    @endif
                                </div>
                                @if(!$notification->is_read)
                                    <div class="w-1.5 h-1.5 rounded-full bg-primary mt-1 shrink-0"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center text-muted-foreground">
                        <svg class="w-8 h-8 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <p class="text-sm">You're all caught up!</p>
                        <p class="text-[10px] mt-1">No new notifications</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endif
@endsection