@extends('layouts.app')

@section('title', 'Notifications - Bira')
@section('hide_sidebar', true)

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Notifications</h1>
            <p class="text-muted-foreground mt-1">Stay updated with your team's activity.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="mark-all-read-btn" class="flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl border border-border-subtle transition-all font-medium text-sm">
                <x-lucide-check-check class="w-4 h-4 text-primary" />
                Mark all as read
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-1 bg-white/5 p-1 rounded-xl w-fit mb-8 border border-white/5">
        <a href="{{ route('notifications.index') }}" class="px-5 py-2 rounded-lg text-sm font-bold transition-all {{ !request()->query('unread') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-muted-foreground hover:text-white' }}">
            All
        </a>
        <a href="{{ route('notifications.index', ['unread' => 1]) }}" class="px-5 py-2 rounded-lg text-sm font-bold transition-all {{ request()->query('unread') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-muted-foreground hover:text-white' }}">
            Unread
            @if(($unreadNotificationCount ?? 0) > 0)
                <span class="ml-1.5 px-1.5 py-0.5 rounded-md bg-white/20 text-[10px]">{{ $unreadNotificationCount }}</span>
            @endif
        </a>
    </div>

    @if($allNotifications->isEmpty())
        <div class="bg-sidebar border border-border-subtle rounded-2xl p-16 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center mb-6">
                <x-lucide-bell-off class="w-10 h-10 text-primary opacity-50" />
            </div>
            <h2 class="text-xl font-bold text-white mb-2">No notifications yet</h2>
            <p class="text-muted-foreground max-w-sm">When you get mentioned or team actions occur, they'll appear here.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($allNotifications as $n)
                @php
                    $notifIcons = [
                        'poker_started'    => 'lucide-clipboard-clock',
                        'poker_completed'  => 'lucide-check-circle',
                        'sprint_started'   => 'lucide-zap',
                        'sprint_completed' => 'lucide-check',
                        'team_added'       => 'lucide-users',
                        'board_added'      => 'lucide-layout',
                        'subteam_added'    => 'lucide-user-plus',
                    ];
                    $iconName = $notifIcons[$n->type] ?? 'lucide-bell';
                    
                    $iconColors = [
                        'poker_started'    => 'text-amber-400 bg-amber-400/10',
                        'poker_completed'  => 'text-green-400 bg-green-400/10',
                        'sprint_started'   => 'text-blue-400 bg-blue-400/10',
                        'sprint_completed' => 'text-emerald-400 bg-emerald-400/10',
                        'team_added'       => 'text-violet-400 bg-violet-400/10',
                        'board_added'      => 'text-cyan-400 bg-cyan-400/10',
                        'subteam_added'    => 'text-pink-400 bg-pink-400/10',
                    ];
                    $iconColor = $iconColors[$n->type] ?? 'text-primary bg-primary/10';
                @endphp
                <div id="notif-card-{{ $n->id }}" class="group relative bg-sidebar border border-border-subtle rounded-2xl p-4 flex items-start gap-4 transition-all hover:bg-white/[0.03] hover:border-white/10 {{ $n->is_read ? 'opacity-70' : 'border-l-primary border-l-2' }}">
                    <div class="shrink-0 w-12 h-12 rounded-xl {{ $iconColor }} flex items-center justify-center">
                        <x-dynamic-component :component="$iconName" class="w-6 h-6" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold text-white {{ $n->is_read ? 'font-medium' : 'font-bold' }}">
                                    {{ $n->title }}
                                </h3>
                                <p class="text-sm text-muted-foreground mt-0.5">
                                    {{ $n->message }}
                                </p>
                            </div>
                            <span class="text-xs text-muted-foreground/60 whitespace-nowrap mt-1">
                                {{ $n->created_at->diffForHumans() }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-4 mt-3">
                            @if($n->link)
                                <a href="{{ route('notifications.read', $n->id) }}" class="text-xs font-bold text-primary hover:text-primary/80 transition-colors flex items-center gap-1.5">
                                    View Details
                                    <x-lucide-arrow-right class="w-3 h-3" />
                                </a>
                            @endif
                            
                            @if(!$n->is_read)
                                <button 
                                    type="button" 
                                    onclick="markAsRead('{{ $n->id }}', this)"
                                    data-url="{{ route('notifications.read', $n->id) }}"
                                    class="mark-read-btn px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg border border-primary/20 transition-all font-bold text-[11px] uppercase tracking-wider"
                                >
                                    Mark as read
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    @if(!$n->is_read)
                        <div id="unread-dot-{{ $n->id }}" class="absolute top-4 right-4 w-2 h-2 rounded-full bg-primary shadow-[0_0_10px_rgba(139,92,246,0.5)]"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $allNotifications->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    function markAsRead(id, btn) {
        const url = btn.dataset.url;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-3 w-3 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ stay: 1 })
        }).then(response => {
            if (response.ok) {
                // Update UI
                const card = document.getElementById(`notif-card-${id}`);
                const dot = document.getElementById(`unread-dot-${id}`);
                
                if (card) {
                    card.classList.add('opacity-70');
                    card.classList.remove('border-l-primary', 'border-l-2');
                }
                if (dot) dot.remove();
                btn.remove();

                // Update the badge in the header
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    let count = parseInt(badge.textContent) || 0;
                    if (count > 0) {
                        count--;
                        if (count === 0) {
                            badge.classList.add('hidden');
                        } else {
                            badge.textContent = count > 99 ? '99+' : count;
                        }
                    }
                }
            } else {
                alert('Failed to mark as read');
                btn.disabled = false;
                btn.innerText = 'Mark as read';
            }
        }).catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerText = 'Mark as read';
        });
    }

    document.getElementById('mark-all-read-btn')?.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        
        fetch('{{ route("notifications.readAll") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
        }).then(() => {
            window.location.reload();
        });
    });
</script>
@endpush
@endsection
