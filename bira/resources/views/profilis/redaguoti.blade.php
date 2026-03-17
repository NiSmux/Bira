@extends('layouts.app')

@section('title', 'Redaguoti profilį – Bira')

@section('hide_sidebar', true)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Header -->
    <div class="mb-10 flex items-center gap-6">
        <a href="{{ route('profilis.rodyti') }}" class="group flex items-center justify-center w-12 h-12 rounded-xl bg-white/5 border border-white/10 text-muted-foreground hover:text-white hover:border-primary/50 transition-all">
            <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">Redaguoti profilį</h1>
            <p class="text-muted-foreground">Atnaujinkite savo asmeninę informaciją</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold">Ištaisykite šias klaidas:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 opacity-90">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-card border border-border-subtle rounded-3xl overflow-hidden shadow-2xl">
        <form action="{{ route('profilis.atnaujinti') }}" method="POST" class="p-8 sm:p-10 space-y-8">
            @csrf
            @method('PUT')

            <!-- Name Input -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-muted-foreground uppercase tracking-wider">Vardas</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="block w-full pl-12 pr-4 py-3.5 bg-white/5 @error('name') border-red-500/50 @else border-white/10 @enderror rounded-2xl text-white placeholder-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                        value="{{ old('name', $user->name) }}"
                        required
                        minlength="2"
                        maxlength="120"
                        placeholder="Jūsų vardas"
                    >
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Input -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-muted-foreground uppercase tracking-wider">El. paštas</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="block w-full pl-12 pr-4 py-3.5 bg-white/5 @error('email') border-red-500/50 @else border-white/10 @enderror rounded-2xl text-white placeholder-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                        value="{{ old('email', $user->email) }}"
                        required
                        maxlength="190"
                        placeholder="epastas@pavyzdys.lt"
                    >
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="pt-6 flex flex-col sm:flex-row gap-4">
                <button type="submit" class="flex-1 flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Išsaugoti pakeitimus
                </button>
                <a href="{{ route('profilis.rodyti') }}" class="flex-1 flex items-center justify-center bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-6 rounded-2xl transition-all border border-white/10 active:scale-[0.98]">
                    Atšaukti
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
