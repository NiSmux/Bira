@extends('layouts.app')

@section('title', $team->name . ' - Komanda')

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $team->name }}</h2>
                <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider border border-primary/20">Team</span>
            </div>
            <p class="text-muted-foreground">{{ $team->description ?: 'Aprašymo nėra' }}</p>
        </div>
        <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white px-4 py-2 rounded-lg font-medium transition-all border border-white/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Atgal
        </a>
    </div>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between transition-opacity duration-300">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold transition-all">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar: Add Member & Boards List -->
        <div class="space-y-8">
            <!-- Add Member Form -->
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Pridėti narį
                </h3>
                
                @if($availableUsers->isEmpty())
                    <div class="p-4 rounded-xl bg-white/5 border border-dashed border-white/10 text-center">
                        <p class="text-xs text-muted-foreground italic">Daugiau narių pridėti negalima.</p>
                    </div>
                @else
                    <form action="{{ route('teams.members.store', $team->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-[10px] font-bold text-muted-foreground uppercase mb-2">Vartotojas</label>
                            <select name="user_id" id="user_id" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all appearance-none" required>
                                <option value="">Pasirinkite vartotoją</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-2.5 rounded-xl font-bold transition-all active:scale-[0.98]">
                            Pridėti prie komandos
                        </button>
                    </form>
                @endif
            </div>

            <!-- Team Boards -->
            <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground mb-6 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Komandos lentos
                </h3>
                <div class="space-y-2">
                    @forelse($team->boards as $board)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 hover:border-primary/30 transition-all group">
                            <span class="text-sm font-medium text-white">{{ $board->name }}</span>
                            <a href="{{ route('boards.show', $board->id) }}" class="text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-muted-foreground italic text-center py-4">Ši komanda dar neturi lentų.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Content: Member Table -->
        <div class="lg:col-span-2">
            <div class="bg-card border border-border-subtle rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between bg-white/5">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-muted-foreground">Komandos nariai</h3>
                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider">{{ $team->members->count() }} nariai</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5 border-b border-border-subtle">
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Vardas</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">El. paštas</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase">Rolė</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-muted-foreground uppercase text-right">Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($team->members as $member)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <span class="text-sm font-medium text-white">{{ $member->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-muted-foreground">{{ $member->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-tighter {{ $member->pivot->role_in_team === 'owner' ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                                            {{ $member->pivot->role_in_team }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($member->pivot->role_in_team !== 'owner')
                                            <form action="{{ route('teams.members.destroy', [$team->id, $member->id]) }}" method="POST" onsubmit="return confirm('Ar tikrai norite pašalinti šį narį?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection