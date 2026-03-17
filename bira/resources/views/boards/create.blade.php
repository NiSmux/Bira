@extends('layouts.app')

@section('title', 'Sukurti naują lentą')

@section('content')
<div class="max-w-3xl mx-auto px-8 py-12">
    <div class="mb-10">
        <a href="{{ route('boards.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Grįžti į sąrašą
        </a>
        <h2 class="text-4xl font-black tracking-tight text-white mb-2">Nauja Kanban lenta</h2>
        <p class="text-muted-foreground text-lg">Sukurkite naują erdvę savo komandos projektams valdyti.</p>
    </div>

    <div class="bg-card border border-border-subtle rounded-3xl p-8 lg:p-10 shadow-2xl overflow-hidden relative group">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
        
        @if($teams->isEmpty())
            <div class="text-center py-10 relative z-10">
                <div class="w-20 h-20 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 mx-auto mb-6 border border-amber-500/20 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">Reikalinga komanda</h3>
                <p class="text-muted-foreground text-lg mb-8 max-w-md mx-auto">Lenta negali egzistuoti be komandos. Sukurkite savo pirmąją komandą, kad galėtumėte kurti projektus.</p>
                <a href="{{ route('teams.create') }}" class="inline-flex items-center gap-3 bg-primary hover:bg-primary/90 text-white px-8 py-3.5 rounded-2xl font-bold transition-all shadow-xl shadow-primary/30 active:scale-95 leading-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Sukurti komandą
                </a>
            </div>
        @else
            <form action="{{ route('boards.store') }}" method="POST" class="space-y-8 relative z-10">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-muted-foreground uppercase tracking-widest mb-3">Lentos pavadinimas</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="w-full bg-background border @error('name') border-red-500/50 @else border-border-subtle @enderror rounded-2xl px-6 py-4 text-white placeholder:text-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-xl font-medium" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus
                               placeholder="Pvz.: Marketingo kampanija 2024">
                        @error('name')
                            <p class="mt-2 text-sm text-red-400 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="team_id" class="block text-sm font-bold text-muted-foreground uppercase tracking-widest mb-3">Priskirti komandai</label>
                        <div class="relative group/select">
                            <select name="team_id" 
                                    id="team_id" 
                                    class="w-full bg-background border @error('team_id') border-red-500/50 @else border-border-subtle @enderror rounded-2xl px-6 py-4 text-white appearance-none focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-lg font-medium cursor-pointer" 
                                    required>
                                <option value="" class="bg-card">Pasirinkite komandą</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('team_id') == $team->id) class="bg-card">
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('team_id')
                            <p class="mt-2 text-sm text-red-400 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="pt-8 border-t border-white/5 flex flex-col sm:flex-row items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto bg-primary hover:bg-primary/90 text-white px-10 py-4 rounded-2xl font-black text-lg transition-all shadow-xl shadow-primary/25 active:scale-95 leading-none">
                        Sukurti lentą
                    </button>
                    <a href="{{ route('boards.index') }}" class="w-full sm:w-auto text-center px-10 py-4 rounded-2xl text-white font-bold hover:bg-white/5 transition-all active:scale-95">
                        Atšaukti
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection