@extends('layouts.app')

@section('title', 'Welcome – Bira')

@section('hide_sidebar', true)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="bg-card border border-border-subtle rounded-3xl p-12 text-center shadow-2xl relative overflow-hidden">
        <!-- Decorative background element -->
        <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
            <h1 class="text-5xl font-extrabold tracking-tight text-white mb-6">
                Welcome to <span class="text-primary">Bira</span>
            </h1>
            
            @auth
                <p class="text-xl text-muted-foreground mb-10">
                    Hello, <span class="text-white font-bold">{{ auth()->user()->name }}</span>! Ready to manage your projects?
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg mx-auto">
                    <a href="{{ route('boards.index') }}" class="flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg shadow-primary/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        My Boards
                    </a>
                    <a href="{{ route('boards.create') }}" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-6 rounded-2xl transition-all border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create New Board
                    </a>
                    <a href="{{ route('teams.index') }}" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-6 rounded-2xl transition-all border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Teams
                    </a>
                    <a href="{{ route('profilis.rodyti') }}" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-6 rounded-2xl transition-all border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        My Profile
                    </a>
                </div>
            @else
                <p class="text-xl text-muted-foreground mb-10">
                    The platform for efficient project management and team collaboration.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 px-8 rounded-2xl transition-all shadow-lg shadow-primary/20">
                        Log In
                    </a>
                    <a href="{{ route('registracija.forma') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-8 rounded-2xl transition-all border border-white/10">
                        Get Started for Free
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection