@extends('layouts.app')

@section('title', 'Report a Bug')

@section('content')
<div class="px-8 py-12 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-2">
        <x-lucide-bug class="w-7 h-7 text-red-400" />
        <h2 class="text-3xl font-bold tracking-tight text-white">Report a Bug</h2>
    </div>
    <p class="text-muted-foreground mb-8">Found something broken? Let us know and we'll fix it.</p>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold">&times;</button>
        </div>
    @endif

    <div class="bg-card border border-border-subtle rounded-2xl p-8 shadow-sm">
        <form action="{{ route('feedback.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="type" value="bug_report">

            <div>
                <label class="block text-xs font-bold text-muted-foreground uppercase mb-1.5">Topic</label>
                <input type="text" name="title" required maxlength="160"
                       value="{{ old('title') }}"
                       placeholder="e.g. Task form closes without saving"
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-red-500/40 transition-all">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-muted-foreground uppercase mb-1.5">Summary and Steps to reproduce <span class="normal-case font-normal">(optional)</span></label>
                <textarea name="description" rows="5" maxlength="5000"
                          placeholder="1. Go to...&#10;2. Click on...&#10;3. See error..."
                          class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-red-500/40 transition-all resize-none">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-red-500/20 active:scale-[0.98]">
                    Submit bug report
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
