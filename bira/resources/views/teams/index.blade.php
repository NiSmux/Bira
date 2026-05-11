@extends('layouts.app')

@section('title', 'Teams')

@section('hide_sidebar')
@endsection

@section('content')
<div class="px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Teams</h2>
            <p class="text-muted-foreground mt-1">Manage your teams and members</p>
        </div>
        <button id="create-team-trigger" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <x-lucide-plus class="w-5 h-5" />
            Create team
        </button>
    </div>

    {{-- Team Creation Modal --}}
    <div id="team-create-modal" class="fixed inset-0 z-[100] items-center justify-center hidden">
        <div class="absolute inset-0 bg-background/80 backdrop-blur-md" id="team-create-backdrop"></div>
        <div class="relative bg-sidebar border border-white/10 rounded-[2.5rem] p-10 w-full max-w-2xl mx-4 shadow-3xl animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div id="team-create-modal-content">
                <div class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
        
        @keyframes zoom-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-in.zoom-in-95 {
            animation: zoom-in 0.2s ease-out;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('team-create-modal');
        const content = document.getElementById('team-create-modal-content');
        const backdrop = document.getElementById('team-create-backdrop');
        
        const openModal = async () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            content.innerHTML = `
                <div class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            `;

            try {
                const response = await fetch("{{ route('teams.create') }}", {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                
                // Extract the script from partial manually
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const script = temp.querySelector('script');
                
                content.innerHTML = html;
                
                if (script) {
                    const newScript = document.createElement('script');
                    newScript.textContent = script.textContent;
                    content.appendChild(newScript);
                }
            } catch (error) {
                content.innerHTML = `<p class="text-red-400">Failed to load form. Please try again.</p>`;
            }
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.getElementById('create-team-trigger')?.addEventListener('click', openModal);
        backdrop?.addEventListener('click', closeModal);
    });
    </script>
    @endpush

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
                <x-lucide-shield-check class="w-5 h-5 text-primary" />
                My teams
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($ownedTeams as $team)
                    <div class="group bg-card border border-border-subtle rounded-2xl p-6 hover:border-primary/50 transition-all shadow-sm">
                        <h4 class="text-xl font-bold text-white mb-2">{{ $team->name }}</h4>
                        <p class="text-muted-foreground text-sm mb-4 line-clamp-2">{{ $team->description ?: 'No description' }}</p>
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/5">
                            <span class="text-xs text-muted-foreground">Members: {{ $team->members->count() }}</span>
                            <a href="{{ route('teams.show', $team->id) }}" class="text-primary hover:text-primary-light font-medium text-sm transition-colors">Manage →</a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                        <p class="text-muted-foreground italic">You haven't created any teams yet.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Member Teams -->
        <section>
            <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <x-lucide-users class="w-5 h-5 text-purple-400" />
                Teams I'm a member of
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($memberTeams as $team)
                    <div class="bg-card border border-border-subtle rounded-2xl p-6 shadow-sm">
                        <h4 class="text-xl font-bold text-white mb-2">{{ $team->name }}</h4>
                        <p class="text-muted-foreground text-sm mb-4">{{ $team->description ?: 'No description' }}</p>
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/5 text-xs text-muted-foreground">
                            <span>Members: {{ $team->members->count() }}</span>
                            <a href="{{ route('teams.show', $team->id) }}" class="text-primary hover:text-primary-light font-medium transition-colors">View →</a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl">
                        <p class="text-muted-foreground italic">You are not a member of any other teams yet.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection