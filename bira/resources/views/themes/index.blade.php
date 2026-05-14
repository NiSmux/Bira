@extends('layouts.app')

@section('title', 'Themes')
@section('hide_sidebar', true)

@section('content')
<div class="px-8 py-12 max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-2">
        <x-lucide-palette class="w-7 h-7 text-primary" />
        <h2 class="text-3xl font-bold tracking-tight text-white">Themes</h2>
    </div>
    <p class="text-muted-foreground mb-10">Choose how Bira looks for you.</p>

    @if(session('success'))
        <div class="alert-container mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button class="alert-close text-green-400/50 hover:text-green-400 text-xl font-bold">&times;</button>
        </div>
    @endif

    @php $current = auth()->user()->theme ?? 'dark'; @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Dark (default) --}}
        <form action="{{ route('themes.update') }}" method="POST">
            @csrf
            <input type="hidden" name="theme" value="dark">
            <button type="submit" class="w-full text-left group">
                <div class="rounded-2xl overflow-hidden border-2 transition-all {{ $current === 'dark' ? 'border-primary shadow-lg shadow-primary/20' : 'border-transparent hover:border-white/20' }}">
                    {{-- Preview --}}
                    <div class="h-36 relative" style="background:#151515">
                        <div class="absolute inset-y-0 left-0 w-10" style="background:#1b1b1b"></div>
                        <div class="absolute top-3 left-14 right-3 h-5 rounded" style="background:#1a1a1a"></div>
                        <div class="absolute top-11 left-14 right-3 h-3 rounded" style="background:#8b5cf620"></div>
                        <div class="absolute top-16 left-14 w-16 h-3 rounded" style="background:#8b5cf6"></div>
                        <div class="absolute top-22 left-14 right-3 h-3 rounded" style="background:#1a1a1a"></div>
                    </div>
                    <div class="px-4 py-3" style="background:#1b1b1b; border-top: 1px solid #ffffff0d">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold" style="color:#fff">Dark</p>
                                <p class="text-xs mt-0.5" style="color:#717182">Default</p>
                            </div>
                            @if($current === 'dark')
                                <span class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#8b5cf6">
                                    <x-lucide-check class="w-3 h-3" style="color:#fff" />
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </button>
        </form>

        {{-- Violet Day --}}
        <form action="{{ route('themes.update') }}" method="POST">
            @csrf
            <input type="hidden" name="theme" value="violet-day">
            <button type="submit" class="w-full text-left group">
                <div class="rounded-2xl overflow-hidden border-2 transition-all {{ $current === 'violet-day' ? 'border-[#7C3AED] shadow-lg shadow-[#7C3AED]/20' : 'border-transparent hover:border-[#7C3AED]/40' }}">
                    <div class="h-36 relative" style="background:#F8FAFC">
                        <div class="absolute inset-y-0 left-0 w-10" style="background:#F1F5F9"></div>
                        <div class="absolute top-3 left-14 right-3 h-5 rounded" style="background:#fff; border:1px solid #E2E8F0"></div>
                        <div class="absolute top-11 left-14 right-3 h-3 rounded" style="background:#7C3AED20"></div>
                        <div class="absolute top-16 left-14 w-16 h-3 rounded" style="background:#7C3AED"></div>
                        <div class="absolute top-22 left-14 right-3 h-3 rounded" style="background:#fff; border:1px solid #E2E8F0"></div>
                    </div>
                    <div class="px-4 py-3" style="background:#F1F5F9; border-top: 1px solid #E2E8F0">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold" style="color:#0F172A">Violet Day</p>
                                <p class="text-xs mt-0.5" style="color:#475569">Light purple</p>
                            </div>
                            @if($current === 'violet-day')
                                <span class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#7C3AED">
                                    <x-lucide-check class="w-3 h-3" style="color:#fff" />
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </button>
        </form>

        {{-- Green --}}
        <form action="{{ route('themes.update') }}" method="POST">
            @csrf
            <input type="hidden" name="theme" value="green">
            <button type="submit" class="w-full text-left group">
                <div class="rounded-2xl overflow-hidden border-2 transition-all {{ $current === 'green' ? 'border-[#10B981] shadow-lg shadow-[#10B981]/20' : 'border-transparent hover:border-[#10B981]/40' }}">
                    <div class="h-36 relative" style="background:#F0FDF4">
                        <div class="absolute inset-y-0 left-0 w-10" style="background:#ECFDF5"></div>
                        <div class="absolute top-3 left-14 right-3 h-5 rounded" style="background:#fff; border:1px solid #D1FAE5"></div>
                        <div class="absolute top-11 left-14 right-3 h-3 rounded" style="background:#10B98120"></div>
                        <div class="absolute top-16 left-14 w-16 h-3 rounded" style="background:#10B981"></div>
                        <div class="absolute top-22 left-14 right-3 h-3 rounded" style="background:#fff; border:1px solid #D1FAE5"></div>
                    </div>
                    <div class="px-4 py-3" style="background:#ECFDF5; border-top: 1px solid #D1FAE5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold" style="color:#064E3B">Green</p>
                                <p class="text-xs mt-0.5" style="color:#065F46">Productivity</p>
                            </div>
                            @if($current === 'green')
                                <span class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#10B981">
                                    <x-lucide-check class="w-3 h-3" style="color:#fff" />
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </button>
        </form>

        {{-- Wood --}}
        <form action="{{ route('themes.update') }}" method="POST">
            @csrf
            <input type="hidden" name="theme" value="wood">
            <button type="submit" class="w-full text-left group">
                <div class="rounded-2xl overflow-hidden border-2 transition-all {{ $current === 'wood' ? 'border-[#8B5E3C] shadow-lg shadow-[#8B5E3C]/20' : 'border-transparent hover:border-[#8B5E3C]/40' }}">
                    <div class="h-36 relative" style="background:#F5EFE7">
                        <div class="absolute inset-y-0 left-0 w-10" style="background:#EDE4D8"></div>
                        <div class="absolute top-3 left-14 right-3 h-5 rounded" style="background:#fff; border:1px solid #E5D6C6"></div>
                        <div class="absolute top-11 left-14 right-3 h-3 rounded" style="background:#8B5E3C20"></div>
                        <div class="absolute top-16 left-14 w-16 h-3 rounded" style="background:#8B5E3C"></div>
                        <div class="absolute top-22 left-14 right-3 h-3 rounded" style="background:#fff; border:1px solid #E5D6C6"></div>
                    </div>
                    <div class="px-4 py-3" style="background:#EDE4D8; border-top: 1px solid #E5D6C6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold" style="color:#2C1F14">Wood</p>
                                <p class="text-xs mt-0.5" style="color:#5A4633">Warm brown</p>
                            </div>
                            @if($current === 'wood')
                                <span class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#8B5E3C">
                                    <x-lucide-check class="w-3 h-3" style="color:#fff" />
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </button>
        </form>

    </div>
</div>
@endsection
