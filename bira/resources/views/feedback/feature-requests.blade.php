@extends('layouts.app')

@section('title', 'Feature Requests')
@section('hide_sidebar', true)

@section('content')
<div class="px-8 py-12 max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-2">
        <x-lucide-lightbulb class="w-7 h-7 text-primary" />
        <h2 class="text-3xl font-bold tracking-tight text-white">Feature Requests</h2>
    </div>
    <p class="text-muted-foreground mb-8">Share ideas and vote on what gets built next.</p>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold">&times;</button>
        </div>
    @endif

    {{-- Submit form --}}
    <div class="bg-card border border-border-subtle rounded-2xl p-6 mb-8 shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-5 flex items-center gap-2">
            <x-lucide-plus-circle class="w-4 h-4" />
            Submit a request
        </h3>
        <form action="{{ route('feedback.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="type" value="feature_request">

            <div>
                <label class="block text-xs font-bold text-muted-foreground uppercase mb-1.5">Title</label>
                <input type="text" name="title" required maxlength="160"
                       value="{{ old('title') }}"
                       placeholder="e.g. Export board to PDF"
                       class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-muted-foreground uppercase mb-1.5">Details <span class="normal-case font-normal">(optional)</span></label>
                <textarea name="description" rows="3" maxlength="5000"
                          placeholder="Describe the feature and why it would be useful..."
                          class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all resize-none">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                    Submit request
                </button>
            </div>
        </form>
    </div>

    {{-- Requests list (Admin only) --}}
    @if(auth()->user()->role_id == 2)
    <div class="mt-12">
        <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-5 flex items-center gap-2">
            <x-lucide-list class="w-4 h-4" />
            Submitted Requests (Admin View)
        </h3>
        <div class="space-y-3">
            @forelse($requests as $item)
                <div class="bg-card border border-border-subtle rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary shrink-0 mt-0.5">
                            {{ strtoupper(substr($item->user->name, 0, 1)) }}{{ strtoupper(substr(strstr($item->user->name, ' ') ?: '', 1, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-white font-semibold text-sm">{{ $item->title }}</span>
                            </div>
                            @if($item->description)
                                <p class="text-muted-foreground text-sm leading-relaxed">{{ $item->description }}</p>
                            @endif
                            <p class="text-xs text-muted-foreground/60 mt-2">
                                {{ $item->user->name }} &middot; {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-muted-foreground">
                    <x-lucide-lightbulb class="w-10 h-10 mx-auto mb-3 opacity-30" />
                    <p class="font-medium">No requests yet.</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
