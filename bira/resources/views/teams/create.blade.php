@extends('layouts.app')

@section('title', 'Sukurti naują komandą')

@section('content')
<div class="max-w-2xl mx-auto px-8 py-12">
    <div class="mb-8">
        <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Grįžti į sąrašą
        </a>
        <h2 class="text-3xl font-bold tracking-tight text-white">Nauja komanda</h2>
        <p class="text-muted-foreground mt-1 text-sm">Sukurkite erdvę bendradarbiavimui su kolegomis.</p>
    </div>

    <div class="bg-card border border-border-subtle rounded-2xl p-8 shadow-sm">
        <form action="{{ route('teams.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-semibold text-white mb-2">Komandos pavadinimas</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="w-full bg-background border @error('name') border-red-500/50 @else border-border-subtle @enderror rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" 
                       value="{{ old('name') }}" 
                       required 
                       placeholder="Pvz.: Marketingo skyrius">
                @error('name')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-white mb-2">Aprašymas (nebūtina)</label>
                <textarea name="description" 
                          id="description" 
                          rows="4" 
                          class="w-full bg-background border @error('description') border-red-500/50 @else border-border-subtle @enderror rounded-xl px-4 py-3 text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all resize-none" 
                          placeholder="Trumpai aprašykite komandos veiklą...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-2 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-6 border-t border-border-subtle flex items-center justify-between">
                <a href="{{ route('teams.index') }}" class="px-6 py-2.5 rounded-xl text-white font-medium hover:bg-white/5 transition-colors">
                    Atšaukti
                </a>
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                    Sukurti komandą
                </button>
            </div>
        </form>
    </div>
</div>
@endsection