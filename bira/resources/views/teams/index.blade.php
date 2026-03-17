@extends('layouts.app')

@section('title', 'Komandos')

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Komandos</h2>
            <p class="text-muted-foreground mt-1">Valdykite savo komandas ir narius</p>
        </div>
        <a href="{{ route('teams.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Sukurti komandą
        </a>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <div class="space-y-12">
        <!-- Owned Teams -->
        <section>
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Mano sukurtos komandos
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($ownedTeams as $team)
                    <div class="group bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/50 transition-all shadow-sm">
                        <h4 class="text-xl font-bold text-white mb-2">{{ $team->name }}</h4>
                        <p class="text-muted-foreground text-sm mb-4 line-clamp-2">{{ $team->description ?: 'Aprašymo nėra' }}</p>
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/5">
                            <span class="text-xs text-muted-foreground">Narių: {{ $team->members->count() }}</span>
                            <a href="{{ route('teams.show', $team->id) }}" class="text-primary hover:text-primary-light font-medium text-sm transition-colors">Valdyti →</a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                        <p class="text-muted-foreground italic">Dar nesukūrėte nei vienos komandos.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Member Teams -->
        <section>
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Komandos, kuriose esu narys
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($memberTeams as $team)
                    <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                        <h4 class="text-xl font-bold text-white mb-2">{{ $team->name }}</h4>
                        <p class="text-muted-foreground text-sm mb-4">{{ $team->description ?: 'Aprašymo nėra' }}</p>
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/5 text-xs text-muted-foreground">
                            <span>Narių: {{ $team->members->count() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                        <p class="text-muted-foreground italic">Kol kas nesate kitų komandų narys.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection