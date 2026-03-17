@extends('layouts.app')

@section('title', 'Mano lentos')

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Mano Kanban lentos</h2>
            <p class="text-muted-foreground mt-1">Peržiūrėkite ir valdykite visus savo projektus</p>
        </div>
        <a href="{{ route('boards.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Sukurti naują lentą
        </a>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($boards as $board)
            <div class="group bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/50 transition-all shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Project</span>
                </div>
                
                <h3 class="text-xl font-bold text-white mb-2">{{ $board->name }}</h3>
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-5 h-5 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-[8px] font-bold text-muted-foreground">
                        {{ strtoupper(substr($board->team->name ?? 'T', 0, 1)) }}
                    </div>
                    <p class="text-sm text-muted-foreground">Komanda: <span class="text-white/80 font-medium">{{ $board->team?->name ?? 'Nėra' }}</span></p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-white/5 mt-auto">
                    <span class="text-xs text-muted-foreground">Sukurta: {{ \Carbon\Carbon::parse($board->created_at)->format('Y-m-d') }}</span>
                    <a href="{{ route('boards.show', $board->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-primary-light font-bold text-sm transition-colors group-hover:gap-2 transition-all">
                        Atidaryti
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-muted-foreground mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Dar neturite jokių lentų</h3>
                <p class="text-muted-foreground mb-8 text-center max-w-sm">Pradėkite sukurdami savo pirmąją Kanban lentą ir pakviesdami savo komandą prisijungti.</p>
                <a href="{{ route('boards.create') }}" class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20">
                    Sukurti pirmąją lentą
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection