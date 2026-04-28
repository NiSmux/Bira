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
            document.addEventListener('click', function(e) {
                if (e.target.closest('.alert-close')) {
                    const alert = e.target.closest('[role="alert"]') || e.target.closest('.alert-container');
                    if (alert) {
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }
                }
            });
        });
    </script>
</body>
</html>
