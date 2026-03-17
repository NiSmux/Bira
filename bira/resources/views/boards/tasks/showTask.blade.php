@extends('layouts.app')

@section('title', $task->title . ' - Detalės')

@section('content')
<div class="max-w-4xl mx-auto px-8 py-12">
    <!-- Breadcrumbs / Back -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('boards.show', $board->id) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Grįžti į lentą
            </a>
            <div class="flex items-center gap-4">
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $task->title }}</h2>
                @if($task->story_points)
                    <span class="px-3 py-1 rounded-lg bg-blue-500/10 text-blue-400 text-sm font-bold border border-blue-500/20">
                        {{ $task->story_points }} SP
                    </span>
                @endif
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('boards.tasks.edit', [$board->id, $task->id]) }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-xl font-medium transition-all border border-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Redaguoti
            </a>
            <form action="{{ route('boards.tasks.destroy', [$board->id, $task->id]) }}" method="POST" onsubmit="return confirm('Ar tikrai norite ištrinti šią užduotį?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2 rounded-xl font-medium transition-all border border-red-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Ištrinti
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (Description) -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-card border border-border-subtle rounded-2xl p-8 shadow-sm h-full">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-4">Aprašymas</h3>
                <div class="text-white text-lg leading-relaxed whitespace-pre-wrap text-left">@if($task->description){!! nl2br(e($task->description)) !!}@else<p class="text-muted-foreground italic">Aprašymo nėra.</p>@endif</div>
            </div>
        </div>

        <!-- Sidebar (Meta Info) -->
        <div class="space-y-6">
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6">Informacija</h3>
                
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase mb-2">Statusas</p>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary/10 text-primary border border-primary/20 font-medium">
                            <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                            {{ $task->status->name ?? 'Nėra' }}
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase mb-2">Tipas</p>
                        <div class="flex items-center gap-2 text-white">
                            <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span class="font-medium">{{ $task->type->name ?? 'Nėra' }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase mb-2">Prioritetas</p>
                        @php
                            $priorityStyles = match(mb_strtolower($task->priority->name ?? 'Default')) {
                                'urgent', 'skubus' => 'bg-red-500/10 text-red-400',
                                'high', 'aukštas' => 'bg-yellow-500/10 text-yellow-400',
                                'medium', 'vidutinis' => 'bg-emerald-500/10 text-emerald-400',
                                'low', 'žemas' => 'bg-blue-500/10 text-blue-400',
                                default => 'bg-gray-500/10 text-gray-400'
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $priorityStyles }}">
                            {{ $task->priority->name ?? 'Nėra' }}
                        </span>
                    </div>

                    <div class="pt-6 border-t border-border-subtle">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-xs border border-primary/20">
                                {{ strtoupper(substr($task->creator->name ?? 'S', 0, 1)) }}{{ strtoupper(substr(strstr($task->creator->name ?? '', ' ') ?: '', 1, 1)) }}
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase">Autorius</p>
                                <p class="text-sm font-medium text-white">{{ $task->creator->name ?? 'Sistemos vartotojas' }}</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-muted-foreground mt-4">Sukurta: {{ $task->created_at ? $task->created_at->format('Y-m-d H:i') : 'Nežinoma' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
