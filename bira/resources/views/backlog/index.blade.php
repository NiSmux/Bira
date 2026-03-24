@extends('layouts.app')

@section('title', 'Global Backlog')

@section('content')
<div class="px-8 py-8 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-sm border border-primary/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    Global Backlog
                </h2>
                <p class="text-sm text-muted-foreground mt-2">View and manage unassigned items across all your boards.</p>
            </div>
        </div>
    </div>

    <div class="space-y-12">
        @forelse($boards as $board)
            @php
                // Get all backlog tasks pre-loaded
                $backlogTasks = $board->items;
            @endphp
            
            <div class="flex flex-col gap-4">
                <!-- Board Header -->
                <div class="flex items-center gap-4 pb-2 border-b border-white/5">
                    <h3 class="text-xl font-bold text-white tracking-tight hover:text-primary transition-colors">
                        <a href="{{ route('boards.show', $board->id) }}">
                            {{ $board->name }}
                        </a>
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-white/5 border border-border-subtle text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                            {{ $board->team->name }}
                        </span>
                    </div>
                    
                    <span class="ml-auto text-sm font-medium text-muted-foreground">
                        {{ $backlogTasks->count() }} task(s)
                    </span>
                </div>

                <!-- Board Backlog List -->
                @if($backlogTasks->isEmpty())
                    <div class="w-full py-8 flex items-center justify-center text-muted-foreground text-sm bg-white/[0.01] border border-dashed border-border-subtle rounded-2xl">
                        No backlog items for this board.
                    </div>
                @else
                    <div class="flex flex-col gap-3">
                        @foreach($backlogTasks as $item)
                        @php
                            $typeName = mb_strtolower($item->type->name ?? '');
                            $blCardBorder = match(true) {
                                $typeName === 'istorija' || $typeName === 'user story' || $typeName === 'story' => 'border-l-[3px] border-l-emerald-500',
                                $typeName === 'užduotis' || $typeName === 'task' => 'border-l-[3px] border-l-blue-500',
                                $typeName === 'klaida' || $typeName === 'bug' => 'border-l-[3px] border-l-red-500',
                                default => 'border-l-[3px] border-l-transparent'
                            };
                        @endphp
                        <div class="group bg-card border border-border-subtle {{ $blCardBorder }} rounded-2xl p-4 hover:border-primary/50 transition-all flex flex-col md:flex-row md:items-center justify-between shadow-sm hover:shadow-md">
                            
                            <!-- Left: Core info -->
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                @php
                                    $priorityStyles = match(mb_strtolower($item->priority->name ?? 'Default')) {
                                        'urgent', 'skubus' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        'high', 'aukštas' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                        'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'low', 'žemas' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $priorityStyles }} border shrink-0 w-24 text-center">
                                    {{ $item->priority->name ?? 'None' }}
                                </span>
                                
                                <a href="{{ route('boards.tasks.show', [$board->id, $item->id]) }}" class="text-white font-semibold truncate text-base hover:text-primary transition-colors">
                                    {{ $item->title }}
                                </a>
                            </div>

                            <!-- Right: Meta & Actions -->
                            <div class="flex items-center gap-6 shrink-0 mt-3 md:mt-0">
                                @if($item->story_points)
                                    <div class="flex items-center gap-2 text-muted-foreground bg-white/5 py-1 px-2.5 rounded-md text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        {{ $item->story_points }}
                                    </div>
                                @endif

                                <div class="flex items-center gap-3">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 rounded-full border-2 border-card bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary" title="Assignee Code">SC</div>
                                    </div>
                                    <div class="text-xs text-muted-foreground pr-4 border-r border-white/5">
                                        Created By: <span class="text-white">{{ $item->creator ? $item->creator->name : 'System' }}</span>
                                    </div>
                                </div>

                                <div>
                                    <a href="{{ route('boards.show', $board->id) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 text-xs font-bold transition-all border border-primary/20">
                                        Go to Board
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="w-full py-16 flex flex-col items-center justify-center bg-white/[0.02] border border-dashed border-border-subtle rounded-3xl">
                <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-muted-foreground mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Your Backlog is Empty</h3>
                <p class="text-muted-foreground">You don't have access to any boards or they have zero backlog items.</p>
                <a href="{{ route('boards.index') }}" class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white font-medium transition-all shadow-lg shadow-primary/20">
                    Browse Boards
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
