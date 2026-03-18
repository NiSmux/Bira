@extends('layouts.app')

@section('title', 'Edit task - ' . $task->title)

@section('content')
<div class="max-w-3xl mx-auto px-8 py-12">
    <div class="mb-8">
        <a href="{{ route('boards.show', $board->id) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to board
        </a>
        <h2 class="text-3xl font-bold tracking-tight text-white">Edit task</h2>
        <p class="text-muted-foreground mt-1 text-sm">Editing task: <span class="text-white font-medium">{{ $task->title }}</span></p>
    </div>

    <div class="bg-card border border-border-subtle rounded-2xl p-8 shadow-sm">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('boards.tasks.update', [$board->id, $task->id]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-semibold text-white mb-2">Title</label>
                <input type="text" 
                       id="title"
                       name="title" 
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                       value="{{ old('title', $task->title) }}" 
                       required>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-white mb-2">Description</label>
                <textarea id="description"
                          name="description" 
                          rows="4" 
                          class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all resize-none">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="status_id" class="block text-sm font-semibold text-white mb-2">Status (Column)</label>
                    <select id="status_id" 
                            name="status_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                            required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="item_type_id" class="block text-sm font-semibold text-white mb-2">Type</label>
                    <select id="item_type_id" 
                            name="item_type_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                            required>
                        @foreach($itemTypes as $type)
                            <option value="{{ $type->id }}" {{ old('item_type_id', $task->item_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="priority_id" class="block text-sm font-semibold text-white mb-2">Priority</label>
                    <select id="priority_id" 
                            name="priority_id" 
                            class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                        <option value="">-- No priority --</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ old('priority_id', $task->priority_id) == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="story_points" class="block text-sm font-semibold text-white mb-2">Story Points</label>
                    <input type="number" 
                           id="story_points"
                           name="story_points" 
                           class="w-full bg-background border border-border-subtle rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all"
                           value="{{ old('story_points', $task->story_points) }}" 
                           min="0" 
                           max="100">
                </div>
            </div>

            <div class="pt-6 border-t border-border-subtle flex items-center justify-between">
                <a href="{{ route('boards.show', $board->id) }}" class="px-6 py-2.5 rounded-xl text-white font-medium hover:bg-white/5 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                    Update task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection