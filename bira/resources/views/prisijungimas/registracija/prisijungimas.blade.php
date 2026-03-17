<!DOCTYPE html>
<html lang="lt" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prisijungimas – Bira</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-background text-foreground antialiased selection:bg-primary/30 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('assets/logo_su_pavadinimu.png') }}" alt="Bira Logo" class="h-12 w-auto">
        </div>
        <h2 class="text-center text-3xl font-extrabold tracking-tight text-white mb-2">
            Sveiki sugrįžę
        </h2>
        <p class="text-center text-sm text-muted-foreground">
            Prisijunkite prie savo paskyros
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-card border border-border-subtle py-8 px-6 shadow-2xl rounded-3xl sm:px-10">
            
            <!-- Success/Error Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->has('login_error'))
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $errors->first('login_error') }}
                </div>
            @endif

            <form action="{{ route('prisijungimas.jungtis') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider pl-1">El. paštas</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="block w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-muted-foreground/30 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                            required
                            placeholder="epastas@pavyzdys.lt"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label for="password" class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider">Slaptažodis</label>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="block w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-muted-foreground/30 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                            required
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-2xl shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-primary/20 transition-all active:scale-[0.98]">
                        Prisijungti
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <p class="text-sm text-muted-foreground">
                    Neturite paskyros? 
                    <a href="{{ route('registracija.forma') }}" class="font-bold text-primary hover:text-primary/80 transition-colors ml-1">
                        Registruotis nemokamai
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer Info -->
        <p class="mt-8 text-center text-xs text-muted-foreground opacity-50 uppercase tracking-[0.2em]">
            &copy; {{ date('Y') }} Bira Platform.
        </p>
    </div>

</body>
</html>