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
                    <button
                        id="open-metrics-btn"
                        data-metrics-url="{{ route('boards.metrics', $effectiveBoardId) }}"
                        class="sidebar-link w-full flex items-center gap-3 px-3 py-2 rounded-lg text-muted-foreground hover:bg-white/5 hover:text-white transition-colors text-left">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span>Metrics</span>
                    </button>
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

    {{-- ══════════════════════════ METRICS DRAWER (global) ══════════════════ --}}
    <div id="metrics-backdrop" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden" onclick="window.closeMetrics && closeMetrics()"></div>
    <div id="metrics-drawer" class="fixed top-0 right-0 h-full w-[420px] bg-[#13131f] border-l border-white/10 z-50 transition-transform duration-300 ease-in-out flex flex-col shadow-2xl" style="transform: translateX(100%);">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h2 class="text-white font-bold text-base">Board Metrics</h2>
            </div>
            <button onclick="window.closeMetrics && closeMetrics()" class="p-1.5 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex border-b border-white/10 shrink-0" id="metrics-tabs">
            <button class="metrics-tab flex-1 py-2.5 text-xs font-semibold uppercase tracking-wider transition-colors text-primary border-b-2 border-primary" data-tab="sprint">Sprint</button>
            <button class="metrics-tab flex-1 py-2.5 text-xs font-semibold uppercase tracking-wider transition-colors text-muted-foreground hover:text-white border-b-2 border-transparent" data-tab="release">Release</button>
            <button class="metrics-tab flex-1 py-2.5 text-xs font-semibold uppercase tracking-wider transition-colors text-muted-foreground hover:text-white border-b-2 border-transparent" data-tab="team">Team</button>
            <button class="metrics-tab flex-1 py-2.5 text-xs font-semibold uppercase tracking-wider transition-colors text-muted-foreground hover:text-white border-b-2 border-transparent" data-tab="user">You</button>
        </div>
        <div id="metrics-loading" class="flex-1 flex items-center justify-center">
            <div class="flex flex-col items-center gap-3 text-muted-foreground">
                <svg class="w-8 h-8 animate-spin text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="text-sm">Loading metrics…</span>
            </div>
        </div>
        <div id="metrics-content" class="flex-1 overflow-y-auto hidden">
            <div class="metrics-panel hidden" data-panel="sprint"><div id="sprint-panel-content" class="p-5 space-y-5"></div></div>
            <div class="metrics-panel hidden" data-panel="release"><div id="release-panel-content" class="p-5 space-y-5"></div></div>
            <div class="metrics-panel hidden" data-panel="team"><div id="team-panel-content" class="p-5 space-y-5"></div></div>
            <div class="metrics-panel hidden" data-panel="user"><div id="user-panel-content" class="p-5 space-y-5"></div></div>
        </div>
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

            // ── Metrics Drawer Logic ─────────────────────────────────────────────
            const openMetricsBtn = document.getElementById('open-metrics-btn');
            const drawer         = document.getElementById('metrics-drawer');
            const backdrop       = document.getElementById('metrics-backdrop');
            
            if (openMetricsBtn && drawer && backdrop) {
                const METRICS_URL = openMetricsBtn.dataset.metricsUrl;
                const loading     = document.getElementById('metrics-loading');
                const content     = document.getElementById('metrics-content');
                const tabs        = document.querySelectorAll('.metrics-tab');
                const panels      = document.querySelectorAll('.metrics-panel');
                let metricsData   = null;
                let activeTab     = 'sprint';

                openMetricsBtn.addEventListener('click', function () {
                    drawer.style.transform = 'translateX(0)';
                    backdrop.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    if (!metricsData) fetchMetrics();
                    else showTab(activeTab);
                });

                window.closeMetrics = function () {
                    drawer.style.transform = 'translateX(100%)';
                    backdrop.classList.add('hidden');
                    document.body.style.overflow = '';
                };

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        activeTab = tab.dataset.tab;
                        tabs.forEach(t => {
                            t.classList.remove('text-primary', 'border-primary');
                            t.classList.add('text-muted-foreground', 'border-transparent');
                        });
                        tab.classList.add('text-primary', 'border-primary');
                        tab.classList.remove('text-muted-foreground', 'border-transparent');
                        if (metricsData) showTab(activeTab);
                    });
                });

                function showTab(name) {
                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                    panels.forEach(p => p.classList.add('hidden'));
                    const panel = document.querySelector(`.metrics-panel[data-panel="${name}"]`);
                    if (panel) panel.classList.remove('hidden');
                }

                function fetchMetrics() {
                    loading.classList.remove('hidden');
                    content.classList.add('hidden');

                    fetch(METRICS_URL, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        metricsData = data;
                        renderSprint(data.sprint);
                        renderRelease(data.release);
                        renderTeam(data.team);
                        renderUser(data.user);
                        showTab(activeTab);
                    })
                    .catch(() => {
                        loading.innerHTML = '<p class="text-red-400 text-sm p-6">Failed to load metrics.</p>';
                    });
                }

                function pct(done, total) { return total > 0 ? Math.round((done / total) * 100) : 0; }
                function progressBar(done, total, color = 'bg-primary') {
                    const p = pct(done, total);
                    return `<div class="w-full bg-white/10 rounded-full h-1.5 mt-1.5"><div class="${color} h-1.5 rounded-full transition-all" style="width:${p}%"></div></div>`;
                }
                function statCard(label, value, sub = '', color = 'text-white') {
                    return `<div class="bg-white/5 rounded-xl p-3 flex flex-col gap-1">
                        <span class="text-[10px] uppercase tracking-wider text-muted-foreground font-medium">${label}</span>
                        <span class="text-xl font-bold ${color}">${value}</span>
                        ${sub ? `<span class="text-[10px] text-muted-foreground">${sub}</span>` : ''}
                    </div>`;
                }
                function sectionTitle(title, icon = '') {
                    return `<div class="flex items-center gap-2 mb-3">${icon}<h3 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">${title}</h3></div>`;
                }
                const typeColors = { 'Bug': 'bg-red-500', 'Task': 'bg-blue-500', 'Story': 'bg-green-500', 'Epic': 'bg-purple-500', 'Untyped': 'bg-gray-500' };
                function typeColor(name) { return typeColors[name] || 'bg-indigo-500'; }

                function renderSprint(s) {
                    const el = document.getElementById('sprint-panel-content');
                    if (!s) {
                        el.innerHTML = `<div class="flex flex-col items-center justify-center py-16 text-center text-muted-foreground gap-3">
                            <svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm">No active sprint</p>
                            <p class="text-xs">Start a sprint from the backlog to see sprint metrics here.</p>
                        </div>`;
                        return;
                    }
                    const itemPct = pct(s.done_items, s.total_items);
                    const ptPct   = pct(s.done_points, s.total_points);
                    const hrPct   = pct(s.done_hours, s.total_hours);
                    const mode    = s.estimation_mode;
                    const daysTag = s.days_left !== null ? (s.overdue ? `<span class="px-2 py-0.5 rounded bg-red-500/20 text-red-400 text-[10px] font-bold">Overdue</span>` : `<span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">${s.days_left}d left</span>`) : '';

                    const byTypeRows = Object.entries(s.by_type).map(([type, d]) => {
                        const p = pct(d.done, d.total);
                        return `<div class="flex items-center gap-2 text-xs">
                            <span class="w-2 h-2 rounded-full shrink-0 ${typeColor(type)}"></span>
                            <span class="flex-1 text-white/80">${type}</span>
                            <span class="text-muted-foreground">${d.done}/${d.total}</span>
                            <div class="w-16 bg-white/10 rounded-full h-1"><div class="${typeColor(type)} h-1 rounded-full" style="width:${p}%"></div></div>
                        </div>`;
                    }).join('');

                    const byStatusRows = Object.entries(s.by_status).map(([st, count]) =>
                        `<div class="flex items-center justify-between text-xs"><span class="text-white/70">${st}</span><span class="px-2 py-0.5 rounded-full bg-white/10 text-white text-[10px] font-bold">${count}</span></div>`
                    ).join('');

                    el.innerHTML = `
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-white font-bold">${s.name}</h3>
                                ${s.goal ? `<p class="text-xs text-muted-foreground mt-0.5 italic">"${s.goal}"</p>` : ''}
                                ${s.start_date ? `<p class="text-[10px] text-muted-foreground mt-1">${s.start_date} → ${s.end_date ?? '?'}</p>` : ''}
                            </div>
                            ${daysTag}
                        </div>
                        <div>
                            ${sectionTitle('Completion')}
                            <div class="flex items-center justify-between mb-1"><span class="text-sm font-bold text-white">${itemPct}%</span><span class="text-xs text-muted-foreground">${s.done_items} / ${s.total_items} items</span></div>
                            ${progressBar(s.done_items, s.total_items, 'bg-primary')}
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            ${mode === 'points' ? statCard('Story Points', `${s.done_points} <span class="text-sm text-muted-foreground font-normal">/ ${s.total_points}</span>`, `${ptPct}% complete`, 'text-purple-400') : statCard('Hours Done', `${s.done_hours}h <span class="text-sm text-muted-foreground font-normal">/ ${s.total_hours}h</span>`, `${hrPct}% complete`, 'text-orange-400')}
                            ${statCard('Remaining', mode === 'points' ? `${s.total_points - s.done_points} pts` : `${(s.total_hours - s.done_hours).toFixed(1)}h`, 'left to complete', 'text-white')}
                        </div>
                        ${Object.keys(s.by_type).length > 0 ? `<div>${sectionTitle('Items by Type')}<div class="space-y-2">${byTypeRows}</div></div>` : ''}
                        ${Object.keys(s.by_status).length > 0 ? `<div>${sectionTitle('Items by Column')}<div class="bg-white/5 rounded-xl p-3 space-y-2">${byStatusRows}</div></div>` : ''}
                    `;
                }

                function renderRelease(r) {
                    const el = document.getElementById('release-panel-content');
                    const maxPts = Math.max(...r.sprint_bars.map(s => s.total || 0), 1);
                    const bars = r.sprint_bars.map(s => {
                        const compH = Math.round((s.completed / maxPts) * 80);
                        const totH  = Math.round((s.total / maxPts) * 80);
                        const statusColor = s.status === 'in_progress' ? 'bg-primary' : (s.status === 'delivered' ? 'bg-emerald-500' : (s.status === 'to_be_released' ? 'bg-yellow-500' : 'bg-white/20'));
                        const shortName = s.name.length > 10 ? s.name.slice(0, 9) + '…' : s.name;
                        return `<div class="flex flex-col items-center gap-1" title="${s.name}: ${s.completed}/${s.total} pts">
                            <span class="text-[9px] text-muted-foreground font-bold">${s.completed}</span>
                            <div class="w-full flex flex-col-reverse" style="height:80px"><div class="${statusColor} w-full rounded-t" style="height:${compH}px"></div><div class="bg-white/10 w-full" style="height:${totH - compH}px"></div></div>
                            <span class="text-[9px] text-muted-foreground text-center leading-tight">${shortName}</span>
                        </div>`;
                    }).join('');

                    el.innerHTML = `
                        <div class="grid grid-cols-3 gap-3">
                            ${statCard('Total Sprints', r.total_sprints, 'all time')}
                            ${statCard('Completed', r.completed_sprints, 'finished sprints', 'text-emerald-400')}
                            ${statCard('Avg Velocity', r.avg_velocity + ' pts', 'per sprint', 'text-purple-400')}
                        </div>
                        ${r.last_sprint ? `<div class="bg-white/5 rounded-xl p-3">${sectionTitle('Last Completed Sprint')}<p class="text-sm font-semibold text-white">${r.last_sprint.name}</p><p class="text-xs text-muted-foreground mt-0.5">${r.last_sprint.points} pts delivered</p></div>` : ''}
                        ${r.sprint_bars.length > 0 ? `<div>${sectionTitle('Velocity Chart (last ' + r.sprint_bars.length + ')')}<div class="grid gap-1" style="grid-template-columns: repeat(${r.sprint_bars.length}, 1fr)">${bars}</div><div class="flex items-center gap-4 mt-3 text-[10px] text-muted-foreground"><span class="flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-primary inline-block"></span> Done</span><span class="flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-white/10 inline-block"></span> Planned</span><span class="flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-500 inline-block"></span> Delivered</span></div></div>` : `<p class="text-sm text-muted-foreground text-center py-6">No sprint history yet.</p>`}
                    `;
                }

                function renderTeam(t) {
                    const el = document.getElementById('team-panel-content');
                    const roleLabels = { product_owner: 'PO', techlead: 'TL', teamlead: 'Lead', developer: 'Dev', tester: 'QA', designer: 'UI', viewer: 'View' };
                    const maxItems = Math.max(...t.members.map(m => m.items), 1);

                    const memberRows = t.members.map(m => {
                        const barW = Math.round((m.items / maxItems) * 100);
                        const initBg = ['bg-purple-600','bg-blue-600','bg-emerald-600','bg-orange-600','bg-pink-600','bg-indigo-600'];
                        const bg = initBg[m.id % initBg.length];
                        const roleLabel = roleLabels[m.role] ?? m.role;
                        return `<div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
                            <div class="w-8 h-8 rounded-full ${bg} flex items-center justify-center text-white text-xs font-bold shrink-0">${m.initials}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2"><span class="text-sm text-white font-medium truncate">${m.name}</span><span class="text-[10px] text-muted-foreground shrink-0">${m.items} items · ${m.pts} pts</span></div>
                                <div class="flex items-center gap-2 mt-1"><span class="text-[9px] uppercase bg-white/10 px-1.5 py-0.5 rounded text-muted-foreground font-bold">${roleLabel}</span><div class="flex-1 bg-white/10 rounded-full h-1"><div class="bg-primary h-1 rounded-full" style="width:${barW}%"></div></div></div>
                            </div>
                        </div>`;
                    }).join('');

                    el.innerHTML = `
                        <div class="grid grid-cols-2 gap-3">
                            ${statCard('Team Members', t.total_members, 'on this board')}
                            ${statCard('Unassigned', t.unassigned_active, 'items in active sprint', t.unassigned_active > 0 ? 'text-amber-400' : 'text-white')}
                        </div>
                        <div>${sectionTitle('Workload Distribution')}<div class="bg-white/5 rounded-xl px-3 py-2">${memberRows || '<p class="text-xs text-muted-foreground py-2 text-center">No members found.</p>'}</div></div>
                    `;
                }

                function renderUser(u) {
                    const el = document.getElementById('user-panel-content');
                    if (u.my_total === 0) {
                        el.innerHTML = `<div class="flex flex-col items-center justify-center py-16 gap-3 text-center text-muted-foreground"><svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path></svg><p class="text-sm">No tasks assigned to you in the active sprint.</p></div>`;
                        return;
                    }
                    const mode = u.estimation_mode;
                    const taskPct = pct(u.my_done, u.my_total);

                    const byTypeRows = Object.entries(u.my_by_type).map(([type, d]) => {
                        const p = pct(d.done, d.total);
                        return `<div class="flex items-center gap-2 text-xs"><span class="w-2 h-2 rounded-full shrink-0 ${typeColor(type)}"></span><span class="flex-1 text-white/80">${type}</span><span class="text-muted-foreground">${d.done}/${d.total}</span><div class="w-16 bg-white/10 rounded-full h-1"><div class="${typeColor(type)} h-1 rounded-full" style="width:${p}%"></div></div></div>`;
                    }).join('');

                    el.innerHTML = `
                        <div>
                            ${sectionTitle('My Sprint Progress')}
                            <div class="flex items-center justify-between mb-1"><span class="text-sm font-bold text-white">${taskPct}%</span><span class="text-xs text-muted-foreground">${u.my_done} / ${u.my_total} done</span></div>
                            ${progressBar(u.my_done, u.my_total, 'bg-primary')}
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            ${mode === 'points' ? statCard('My Points', `${u.my_pts_done} <span class="text-sm font-normal text-muted-foreground">/ ${u.my_pts_total}</span>`, `${pct(u.my_pts_done, u.my_pts_total)}% done`, 'text-purple-400') : statCard('My Hours', `${u.my_hrs_done}h <span class="text-sm font-normal text-muted-foreground">/ ${u.my_hrs_total}h</span>`, `${pct(u.my_hrs_done, u.my_hrs_total)}% done`, 'text-orange-400')}
                            ${statCard('Tasks Left', u.my_total - u.my_done, 'remaining in sprint', (u.my_total - u.my_done) === 0 ? 'text-emerald-400' : 'text-white')}
                        </div>
                        ${Object.keys(u.my_by_type).length > 0 ? `<div>${sectionTitle('My Items by Type')}<div class="space-y-2">${byTypeRows}</div></div>` : ''}
                    `;
                }
            }

        });
    </script>
</body>
</html>
