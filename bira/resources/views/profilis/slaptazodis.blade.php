@extends('layouts.app')

@section('title', 'Change password – Bira')

@section('hide_sidebar', true)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Header -->
    <div class="mb-10 flex items-center gap-6">
        <a href="{{ route('profilis.rodyti') }}" class="group flex items-center justify-center w-12 h-12 rounded-xl bg-white/5 border border-white/10 text-muted-foreground hover:text-white hover:border-primary/50 transition-all">
            <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">Change password</h1>
            <p class="text-muted-foreground">Security first</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold">Fix these errors:</span>
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
        <form action="{{ route('profilis.slaptazodis.keisti') }}" method="POST" class="p-8 sm:p-10 space-y-8">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div class="space-y-2">
                <label for="dabartinis_slaptazodis" class="block text-sm font-semibold text-muted-foreground uppercase tracking-wider">Current password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <input
                        type="password"
                        id="dabartinis_slaptazodis"
                        name="dabartinis_slaptazodis"
                        class="block w-full pl-12 pr-4 py-3.5 bg-white/5 @error('dabartinis_slaptazodis') border-red-500/50 @else border-white/10 @enderror rounded-2xl text-white placeholder-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                        required
                        placeholder="Enter current password"
                    >
                </div>
            </div>

            <!-- New Password -->
            <div class="space-y-2">
                <label for="naujas_slaptazodis" class="block text-sm font-semibold text-muted-foreground uppercase tracking-wider">New password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 11-7.743-5.743L11 3l-2 2H5a2 2 0 00-2 2v10a2 2 0 002 2h2v2l2-2h2a2 2 0 002-2v-7a2 2 0 012-2h2a2 2 0 012 2v3m2 4l-2 2m2-2l2 2m-2-2l2-2m-2 2l-2-2"></path></svg>
                    </div>
                    <input
                        type="password"
                        id="naujas_slaptazodis"
                        name="naujas_slaptazodis"
                        class="block w-full pl-12 pr-4 py-3.5 bg-white/5 @error('naujas_slaptazodis') border-red-500/50 @else border-white/10 @enderror rounded-2xl text-white placeholder-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                        required
                        minlength="6"
                        placeholder="At least 6 characters"
                        oninput="checkStrength(this.value)"
                    >
                </div>
                <!-- Strength Indicator -->
                <div class="space-y-1.5 pt-1">
                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                        <div id="strengthFill" class="h-full w-0 transition-all duration-500 ease-out"></div>
                    </div>
                    <p id="strengthText" class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground"></p>
                </div>
            </div>

            <!-- Confirmation -->
            <div class="space-y-2">
                <label for="naujas_slaptazodis_confirmation" class="block text-sm font-semibold text-muted-foreground uppercase tracking-wider">Confirm new password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-muted-foreground group-focus-within:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <input
                        type="password"
                        id="naujas_slaptazodis_confirmation"
                        name="naujas_slaptazodis_confirmation"
                        class="block w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-muted-foreground/50 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                        required
                        placeholder="Confirm new password"
                    >
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-6 flex flex-col sm:flex-row gap-4">
                <button type="submit" class="flex-1 flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Change password
                </button>
                <a href="{{ route('profilis.rodyti') }}" class="flex-1 flex items-center justify-center bg-white/5 hover:bg-white/10 text-white font-bold py-4 px-6 rounded-2xl transition-all border border-white/10 active:scale-[0.98]">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function checkStrength(value) {
        const fill = document.getElementById('strengthFill');
        const text = document.getElementById('strengthText');
        let score = 0;
        
        if (value.length >= 6) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;
        if (value.length >= 10) score++;

        const configs = [
            { width: '0%', color: 'bg-white/10', label: '' },
            { width: '20%', color: 'bg-red-500', label: 'Very weak' },
            { width: '40%', color: 'bg-orange-500', label: 'Weak' },
            { width: '60%', color: 'bg-yellow-500', label: 'Medium' },
            { width: '80%', color: 'bg-emerald-500', label: 'Strong' },
            { width: '100%', color: 'bg-primary', label: 'Very strong' }
        ];

        const config = configs[Math.min(score, 5)];
        
        fill.className = `h-full transition-all duration-500 ease-out ${config.color}`;
        fill.style.width = config.width;
        text.textContent = config.label;
        text.className = `text-[10px] font-bold uppercase tracking-wider ${config.color.replace('bg-', 'text-')}`;
    }
</script>
@endsection
