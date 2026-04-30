<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bira')</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar-link.active {
            background-color: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
            border-right: 2px solid #8b5cf6;
        }
    </style>
</head>
<body class="bg-background text-foreground antialiased selection:bg-primary/30">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @unless(View::hasSection('hide_sidebar'))
        <aside class="w-64 bg-sidebar border-r border-border-subtle flex flex-col shrink-0">
            <a href="{{ route('pagrindinis') }}" class="p-6 flex items-center gap-3 hover:opacity-80 transition-opacity">
                <img src="{{ asset('assets/logo_su_pavadinimu.png') }}" alt="Bira Logo" class="h-8 w-auto">
            </a>
            
            <nav class="flex-1 px-4 space-y-1 mt-4">
                @php
                    // Resolve context objects for sidebar if they are missing from the view
                    $_board = isset($board) ? $board : (request()->query('board_id') ? \App\Models\Board::find(request()->query('board_id')) : null);
                    
                    // If still no board, check if there's a poker session context
                    if (!$_board && isset($session) && $session instanceof \App\Models\PokerSession && $session->board_id) {
                        $_board = $session->board;
                    }

                    $_team = isset($team) ? $team : (isset($_board) ? $_board->team : (request()->query('team_id') ? \App\Models\Team::find(request()->query('team_id')) : null));
                    
                    // If still no team, check if there's a poker session context
                    if (!$_team && isset($session) && $session instanceof \App\Models\PokerSession) {
                        $_team = $session->team;
                    }

                    $effectiveBoardId = $_board->id ?? null;
                    $currentTeam = $_team;
                    $teamBoards = $currentTeam ? $currentTeam->boards : collect();

                    $boardUrl = $effectiveBoardId
                        ? route('boards.show', $effectiveBoardId)
                        : route('boards.index');
                @endphp
                
                <div>
                    <a href="{{ $boardUrl }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-muted-foreground hover:bg-white/5 hover:text-white transition-colors {{ request()->is('boards*') && !request()->is('boards/*/reports*') ? 'active' : '' }}">
                        <x-lucide-layout-dashboard class="w-5 h-5" />
                        <span>Board</span>
                    </a>
                </div>

                @if($currentTeam)
                    <div class="pt-2">
                        @php
                            $backlogParams = ['team_id' => $currentTeam->id];
                            if ($effectiveBoardId) {
                                $backlogParams['board_id'] = $effectiveBoardId;
                            }
                        @endphp
                        <a href="{{ route('backlog.index', $backlogParams) }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-muted-foreground hover:bg-white/5 hover:text-white transition-colors {{ request()->routeIs('backlog.*') ? 'active' : '' }}">
                            <x-lucide-folders class="w-5 h-5" />
                            <span>Backlog</span>
                        </a>
                    </div>
                @endif

                <a href="{{ $currentTeam ? route('teams.show', array_merge(['team' => $currentTeam->id], $effectiveBoardId ? ['board_id' => $effectiveBoardId] : [])) : route('teams.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-muted-foreground hover:bg-white/5 hover:text-white transition-colors {{ request()->is('teams*') ? 'active' : '' }}">
                    <x-lucide-users class="w-5 h-5" />
                    <span>Team</span>
                </a>

                @php
                    $reportsUrl = $effectiveBoardId
                        ? route('reports.index', $effectiveBoardId)
                        : route('boards.index');
                @endphp
                @if($effectiveBoardId)
                    <a href="{{ $reportsUrl }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-muted-foreground hover:bg-white/5 hover:text-white transition-colors {{ request()->is('boards/*/reports*') ? 'active' : '' }}">
                        <x-lucide-bar-chart-3 class="w-5 h-5" />
                        <span>Reports</span>
                    </a>
                @endif

                @php
                    $pokerParams = [];
                    if ($currentTeam) {
                        $pokerParams['team_id'] = $currentTeam->id;
                        if ($effectiveBoardId) {
                            $pokerParams['board_id'] = $effectiveBoardId;
                        }
                    } elseif ($effectiveBoardId && $_board) {
                        $pokerParams = ['team_id' => $_board->team_id, 'board_id' => $effectiveBoardId];
                    }
                @endphp
                @if($currentTeam || $effectiveBoardId)
                    <a href="{{ route('poker.index', $pokerParams) }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-muted-foreground hover:bg-white/5 hover:text-white transition-colors {{ request()->is('poker*') ? 'active' : '' }}">
                        <x-lucide-clipboard-clock class="w-5 h-5" />
                        <span>Planning Poker</span>
                    </a>
                @endif
            </nav>

            
        </aside>
        @endunless

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Top Navigation -->
            <header class="h-16 bg-sidebar border-b border-border-subtle flex items-center justify-between px-8 shrink-0">
                <div class="flex items-center gap-8">
                    @if(View::hasSection('hide_sidebar'))
                        <a href="{{ route('pagrindinis') }}" class="mr-4 hover:opacity-80 transition-opacity">
                            <img src="{{ asset('assets/logo_su_pavadinimu.png') }}" alt="Bira Logo" class="h-8 w-auto">
                        </a>
                    @endif
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('pagrindinis') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('pagrindinis') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-white/5 hover:text-white' }}">Main</a>
                        <a href="{{ route('boards.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('boards*') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-white/5 hover:text-white' }}">Boards</a>
                        <a href="{{ route('teams.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('teams*') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-white/5 hover:text-white' }}">Teams</a>
                    </nav>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('profilis.rodyti') }}" class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-medium text-[10px] tracking-tighter hover:bg-primary/30 transition-all border border-primary/20" title="My profile">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name, ' ') ?: '', 1, 1)) }}
                    </a>

                    {{-- Notifications bell --}}
                    <div class="relative" id="notif-wrapper">
                        <button id="notif-btn" class="p-2 rounded-lg text-muted-foreground hover:text-white hover:bg-white/5 transition-all relative" title="Notifications">
                            <x-lucide-bell class="w-5 h-5" />
                            @if(($unreadNotificationCount ?? 0) > 0)
                                <span id="notif-badge" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold px-1 shadow-lg shadow-red-500/30 animate-pulse">
                                    {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                </span>
                            @else
                                <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold px-1 shadow-lg shadow-red-500/30"></span>
                            @endif
                        </button>
                        <div id="notif-dropdown" class="hidden absolute right-0 top-full mt-2 w-96 bg-sidebar border border-border-subtle rounded-xl shadow-2xl z-50 overflow-hidden">
                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 border-b border-border-subtle">
                                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                    <x-lucide-bell class="w-4 h-4 text-primary" />
                                    Notifications
                                </h3>
                                <button id="notif-mark-all" class="text-xs text-primary hover:text-primary/80 font-medium transition-colors">
                                    Mark all as read
                                </button>
                            </div>
                            {{-- Notification list --}}
                            <div id="notif-list" class="max-h-[400px] overflow-y-auto">
                                <div class="flex items-center justify-center py-8 text-muted-foreground text-sm">
                                    Loading...
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Settings dropdown --}}
                    <div class="relative" id="settings-menu-wrapper">
                        <button id="settings-menu-btn" class="p-2 rounded-lg text-muted-foreground hover:text-white hover:bg-white/5 transition-all" title="Settings">
                            <x-lucide-settings class="w-5 h-5" />
                        </button>
                        <div id="settings-menu"
                             class="hidden absolute right-0 top-full mt-2 w-52 bg-sidebar border border-border-subtle rounded-xl shadow-xl z-50 overflow-hidden py-1">
                            <a href="{{ route('feedback.feature-requests') }}"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm text-muted-foreground hover:text-white hover:bg-white/5 transition-colors {{ request()->routeIs('feedback.feature-requests') ? 'text-white bg-white/5' : '' }}">
                                <x-lucide-lightbulb class="w-4 h-4 shrink-0" />
                                Feature Requests
                            </a>
                            <a href="{{ route('feedback.bug-report') }}"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm text-muted-foreground hover:text-white hover:bg-white/5 transition-colors {{ request()->routeIs('feedback.bug-report') ? 'text-white bg-white/5' : '' }}">
                                <x-lucide-bug class="w-4 h-4 shrink-0" />
                                Report a Bug
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('atsijungti') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-muted-foreground hover:text-white hover:bg-white/5 transition-all" title="Logout">
                            <x-lucide-log-out class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-auto">
                @yield('content')
            </div>
        </main>
    </div>
    
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // ── Notification type icons (SVG paths) ──
            const notifIcons = {
                poker_started:    '<svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
                poker_completed:  '<svg class="w-4 h-4 shrink-0 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                sprint_started:   '<svg class="w-4 h-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                sprint_completed: '<svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                team_added:       '<svg class="w-4 h-4 shrink-0 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                board_added:      '<svg class="w-4 h-4 shrink-0 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/></svg>',
                subteam_added:    '<svg class="w-4 h-4 shrink-0 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            };
            const defaultIcon = '<svg class="w-4 h-4 shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>';

            function renderNotifications(data) {
                const list = document.getElementById('notif-list');
                if (!data.length) {
                    list.innerHTML = '<div class="flex flex-col items-center justify-center py-10 text-muted-foreground"><svg class="w-10 h-10 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg><span class="text-sm">No notifications</span></div>';
                    return;
                }
                list.innerHTML = data.map(n => {
                    const icon = notifIcons[n.type] || defaultIcon;
                    const unreadClass = n.is_read ? 'opacity-60' : 'bg-primary/5 border-l-2 border-l-primary';
                    const titleWeight = n.is_read ? 'font-normal text-muted-foreground' : 'font-semibold text-white';
                    return `<a href="/notifications/${n.id}/read" data-notif-id="${n.id}" class="notif-item flex items-start gap-3 px-4 py-3 hover:bg-white/5 transition-colors cursor-pointer ${unreadClass}">
                        <div class="mt-0.5">${icon}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm ${titleWeight} truncate">${n.title}</p>
                            <p class="text-xs text-muted-foreground mt-0.5 line-clamp-2">${n.message || ''}</p>
                            <p class="text-[11px] text-muted-foreground/60 mt-1">${n.time_ago}</p>
                        </div>
                        ${!n.is_read ? '<span class="mt-1.5 w-2 h-2 rounded-full bg-primary shrink-0"></span>' : ''}
                    </a>`;
                }).join('');

                // Handle click: POST to mark-as-read, then redirect
                list.querySelectorAll('.notif-item').forEach(el => {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.notifId;
                        // Submit a POST form to mark as read (which then redirects)
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/notifications/${id}/read`;
                        form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
                        document.body.appendChild(form);
                        form.submit();
                    });
                });
            }

            function loadNotifications() {
                const list = document.getElementById('notif-list');
                list.innerHTML = '<div class="flex items-center justify-center py-8 text-muted-foreground text-sm">Loading...</div>';
                fetch('/notifications', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => renderNotifications(data))
                    .catch(() => {
                        list.innerHTML = '<div class="flex items-center justify-center py-8 text-muted-foreground text-sm">Failed to load</div>';
                    });
            }

            // Mark all as read
            document.getElementById('notif-mark-all')?.addEventListener('click', function() {
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                }).then(() => {
                    loadNotifications();
                    const badge = document.getElementById('notif-badge');
                    if (badge) { badge.classList.add('hidden'); badge.textContent = ''; }
                });
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.alert-close')) {
                    const alert = e.target.closest('[role="alert"]') || e.target.closest('.alert-container');
                    if (alert) {
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }
                }

                // Notification dropdown toggle
                const notifBtn = document.getElementById('notif-btn');
                const notifDropdown = document.getElementById('notif-dropdown');
                const notifWrapper = document.getElementById('notif-wrapper');
                if (notifBtn && notifDropdown) {
                    if (e.target.closest('#notif-btn')) {
                        const wasHidden = notifDropdown.classList.contains('hidden');
                        notifDropdown.classList.toggle('hidden');
                        if (wasHidden) loadNotifications();
                    } else if (notifWrapper && !notifWrapper.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                    }
                }

                // Settings dropdown toggle
                const btn = document.getElementById('settings-menu-btn');
                const menu = document.getElementById('settings-menu');
                const wrapper = document.getElementById('settings-menu-wrapper');
                if (btn && menu) {
                    if (e.target.closest('#settings-menu-btn')) {
                        menu.classList.toggle('hidden');
                    } else if (wrapper && !wrapper.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                }
            });
        });
    </script>
</body>
</html>
