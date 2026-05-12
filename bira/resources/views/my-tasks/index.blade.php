@extends('layouts.app')

@section('title', 'My Active Tasks')

@section('content')
<div class="px-8 py-10 max-w-[1200px] mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                My Active Tasks
            </h2>
            <p class="text-muted-foreground mt-1">Tasks assigned to you that are currently in progress — across all boards.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-lg bg-blue-500/10 text-blue-400 text-sm font-bold border border-blue-500/20">
                {{ $tasks->total() }} task{{ $tasks->total() !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('my-tasks.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
        {{-- Search --}}
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-1.5">Search</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter by title..."
                    class="w-full bg-background border border-border-subtle rounded-xl pl-9 pr-4 py-2 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
            </div>
        </div>

        {{-- Board filter --}}
        <div class="min-w-[180px]">
            <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-1.5">Board</label>
            <select name="board_id" class="w-full bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <option value="">All boards</option>
                @foreach($userBoards as $b)
                    <option value="{{ $b->id }}" @selected(request('board_id') == $b->id)>
                        {{ $b->team ? $b->team->name . ' / ' : '' }}{{ $b->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Priority filter --}}
        <div class="min-w-[150px]">
            <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-1.5">Priority</label>
            <select name="priority_id" class="w-full bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <option value="">All priorities</option>
                @foreach($priorities as $p)
                    <option value="{{ $p->id }}" @selected(request('priority_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Sort --}}
        <div class="min-w-[160px]">
            <label class="block text-[10px] font-bold text-muted-foreground uppercase mb-1.5">Sort by</label>
            <select name="sort" class="w-full bg-background border border-border-subtle rounded-xl px-3 py-2 text-sm text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <option value="updated_at" @selected(request('sort', 'updated_at') === 'updated_at')>Last updated</option>
                <option value="created_at" @selected(request('sort') === 'created_at')>Created</option>
                <option value="story_points" @selected(request('sort') === 'story_points')>Story points</option>
                <option value="estimated_hours" @selected(request('sort') === 'estimated_hours')>Estimated hours</option>
            </select>
        </div>
        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

        <div class="flex gap-2">
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-xl font-bold text-sm transition-all active:scale-[0.98]">
                Apply
            </button>
            @if(request()->hasAny(['search', 'board_id', 'priority_id', 'sort']))
                <a href="{{ route('my-tasks.index') }}" class="bg-white/5 hover:bg-white/10 border border-white/10 text-white px-4 py-2 rounded-xl font-bold text-sm transition-all">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Task list --}}
    @if($tasks->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center text-muted-foreground bg-card border border-border-subtle rounded-2xl">
            <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            <p class="font-semibold text-white mb-1">No active tasks found</p>
            <p class="text-sm">You have no in-progress tasks assigned to you right now.</p>
        </div>
    @else
        <div class="bg-card border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-white/[0.03] border-b border-border-subtle">
                        <th class="px-5 py-3.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Task</th>
                        <th class="px-5 py-3.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider hidden md:table-cell">Board / Sprint</th>
                        <th class="px-5 py-3.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider hidden lg:table-cell">Status</th>
                        <th class="px-5 py-3.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider hidden lg:table-cell">Effort</th>
                        <th class="px-5 py-3.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider hidden md:table-cell">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($tasks as $task)
                    @php
                        $priorityStyles = match(mb_strtolower($task->priority->name ?? '')) {
                            'urgent', 'skubus'    => ['dot' => 'bg-red-500',     'badge' => 'bg-red-500/10 text-red-400 border-red-500/20'],
                            'high', 'aukštas'     => ['dot' => 'bg-yellow-500',  'badge' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'],
                            'medium', 'vidutinis' => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                            'low', 'žemas'        => ['dot' => 'bg-blue-500',    'badge' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
                            default               => ['dot' => 'bg-gray-400',    'badge' => 'bg-gray-500/10 text-gray-400 border-gray-500/20'],
                        };
                        $taskBoard = $task->boards->first();
                        $boardMode = $taskBoard?->estimation_mode ?? 'points';
                        $effort = $boardMode === 'hours'
                            ? ($task->estimated_hours ? $task->estimated_hours . 'h' : null)
                            : ($task->story_points ? $task->story_points . ' SP' : null);
                    @endphp
                    <tr class="hover:bg-white/[0.02] transition-colors group">
                        <td class="px-5 py-4">
                            <a href="{{ $taskBoard ? route('boards.tasks.show', [$taskBoard->id, $task->id]) . '?redirect_to=' . urlencode(route('my-tasks.index')) : '#' }}"
                               class="block group-hover:text-primary transition-colors">
                                <div class="flex items-start gap-3">
                                    {{-- Priority dot --}}
                                    <div class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $priorityStyles['dot'] }}"></div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-white truncate max-w-[340px] group-hover:text-primary transition-colors">{{ $task->title }}</p>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            @if($task->type)
                                                @php $typeColor = $task->type->color ?? null; @endphp
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                                    @if($typeColor)
                                                        <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $typeColor }}"></span>
                                                    @endif
                                                    {{ $task->type->icon ? $task->type->icon . ' ' : '' }}{{ $task->type->name }}
                                                </span>
                                            @endif
                                            @if($task->priority)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-tighter border {{ $priorityStyles['badge'] }}">
                                                    {{ $task->priority->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell">
                            @if($taskBoard)
                                <p class="text-sm text-white font-medium">{{ $taskBoard->name }}</p>
                                @if($taskBoard->team)
                                    <p class="text-[10px] text-muted-foreground">{{ $taskBoard->team->name }}</p>
                                @endif
                                @if($task->sprint)
                                    <span class="inline-flex items-center gap-1 mt-1 text-[10px] text-primary bg-primary/10 px-1.5 py-0.5 rounded font-medium">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        {{ $task->sprint->name }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted-foreground text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            @if($task->status)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/10 text-primary border border-primary/20 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                    {{ $task->status->name }}
                                </span>
                            @else
                                <span class="text-muted-foreground text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            @if($effort)
                                <span class="text-sm font-bold text-white">{{ $effort }}</span>
                            @else
                                <span class="text-muted-foreground text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell">
                            <span class="text-xs text-muted-foreground">{{ $task->updated_at ? $task->updated_at->diffForHumans() : '—' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tasks->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $tasks->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
