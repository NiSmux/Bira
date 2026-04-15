@extends('layouts.app')

@section('title', (isset($board) ? $board->name . ' - Backlog' : (isset($team) ? $team->name . ' - Team Backlog' : 'Global Backlog')))

@section('content')
    <div class="px-8 py-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-sm border border-primary/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                        {{ isset($board) ? $board->name . ' Backlog' : (isset($team) ? $team->name . ' Backlog' : 'Global Backlog') }}
                    </h2>
                    <p class="text-sm text-muted-foreground mt-2">
                        @if(isset($board))
                            Manage backlog items for the <strong>{{ $board->name }}</strong> board.
                        @elseif(isset($team))
                            View and manage unassigned items across all <strong>{{ $team->name }}</strong> boards.
                        @else
                            View and manage unassigned items across all your boards.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-12">
            @forelse($boards as $backlogBoard)
                @php
                    // Get all backlog tasks pre-loaded
                    $backlogTasks = $backlogBoard->items;
                @endphp

                <div class="flex flex-col gap-4">
                    <!-- Board Header -->
                    <div class="flex items-center gap-4 pb-2 border-b border-white/5">
                        <h3 class="text-xl font-bold text-white tracking-tight hover:text-primary transition-colors">
                            <a href="{{ route('boards.show', $backlogBoard->id) }}">
                                {{ $backlogBoard->name }}
                            </a>
                        </h3>
                        <div class="flex items-center gap-2">
                            <span
                                class="px-2 py-0.5 rounded bg-white/5 border border-border-subtle text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                {{ $backlogBoard->team->name }}
                            </span>
                        </div>

                        <span class="ml-auto text-sm font-medium text-muted-foreground">
                            {{ $backlogTasks->count() }} task(s)
                        </span>
                    </div>

                    <!-- Board Backlog List -->
                    @if($backlogTasks->isEmpty())
                        <div
                            class="w-full py-8 flex items-center justify-center text-muted-foreground text-sm bg-white/5 border border-dashed border-border-subtle rounded-2xl">
                            No backlog items for this board.
                        </div>
                    @else
                        <div class="flex flex-col gap-3 backlog-tasks">
                            @foreach($backlogTasks as $item)
                                @include('boards.tasks._task_card', ['item' => $item, 'backlogBoard' => $backlogBoard, 'permissionLevel' => 'viewer'])
                                {{-- Assuming viewer for global backlog unless otherwise specified --}}
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div
                    class="w-full py-16 flex flex-col items-center justify-center bg-white/5 border border-dashed border-border-subtle rounded-3xl">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-muted-foreground mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Your Backlog is Empty</h3>
                    <p class="text-muted-foreground">You don't have access to any boards or they have zero backlog items.</p>
                    <a href="{{ route('boards.index') }}"
                        class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white font-medium transition-all shadow-lg shadow-primary/20">
                        Browse Boards
                    </a>
                </div>
            @endforelse
        </div>
    </div>
@endsection