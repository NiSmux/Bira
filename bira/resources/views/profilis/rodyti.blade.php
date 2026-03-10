<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mano profilis – Bira</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --surface: #f8f9ff;
            --card-bg: #ffffff;
            --text-muted-soft: #64748b;
        }

        body {
            background-color: var(--surface);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }

        /* ── Navbar ── */
        .navbar-bira {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 0.75rem 1.5rem;
        }
        .navbar-bira .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: #fff !important;
            letter-spacing: -0.5px;
        }
        .navbar-bira .nav-link-btn {
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: background 0.2s, color 0.2s;
        }
        .navbar-bira .nav-link-btn:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        /* ── Hero / Profilio antraštė ── */
        .profile-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            padding: 2.5rem 0 4rem;
            position: relative;
            overflow: hidden;
        }
        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0; right: 0;
            height: 50px;
            background: var(--surface);
            clip-path: ellipse(60% 100% at 50% 100%);
        }
        .avatar-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
            border: 3px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .profile-hero h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .profile-hero .badge-role {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 20px;
            padding: 0.3rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* ── Statistikos kortelės ── */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.4rem 1.6rem;
            box-shadow: 0 1px 12px rgba(79,70,229,0.08);
            border: 1px solid #e8e6ff;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(79,70,229,0.14);
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-icon.blue   { background: #ede9fe; color: #4f46e5; }
        .stat-icon.green  { background: #dcfce7; color: #16a34a; }
        .stat-icon.purple { background: #fae8ff; color: #a21caf; }
        .stat-value {
            font-size: 1.9rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted-soft);
            margin-top: 0.2rem;
        }

        /* ── Info kortelė ── */
        .info-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 12px rgba(79,70,229,0.06);
            border: 1px solid #e8e6ff;
            height: 100%;
        }
        .info-card .section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted-soft);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.7rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .info-icon {
            color: var(--primary);
            font-size: 1rem;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .info-row .info-label {
            font-size: 0.78rem;
            color: var(--text-muted-soft);
            margin-bottom: 0.1rem;
        }
        .info-row .info-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: #1e293b;
        }

        /* ── Komandos ── */
        .team-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ede9fe;
            color: #4f46e5;
            border-radius: 10px;
            padding: 0.45rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 500;
            margin: 0.2rem;
            border: 1px solid #c4b5fd;
        }

        /* ── Paskutinės užduotys ── */
        .task-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 0;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .task-row:last-child { border-bottom: none; }
        .task-type-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            font-weight: 600;
            white-space: nowrap;
            background: #ede9fe;
            color: #4f46e5;
        }
        .priority-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .p-skubus  { background: #ef4444; }
        .p-aukštas { background: #f97316; }
        .p-vidutinis{ background: #eab308; }
        .p-žemas   { background: #22c55e; }
        .p-none    { background: #cbd5e1; }

        .status-pill {
            font-size: 0.72rem;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-weight: 500;
            white-space: nowrap;
        }
        .status-done { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fff7ed; color: #ea580c; }

        /* ── Veiksmai ── */
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 10px;
            padding: 0.55rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .action-btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .action-btn-primary:hover {
            background: var(--primary-light);
            color: #fff;
            transform: translateY(-1px);
        }
        .action-btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1.5px solid var(--primary);
        }
        .action-btn-outline:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Alerts */
        .alert-success-custom {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
    </style>
</head>
<body>

{{-- ── NAVBAR ── --}}
<nav class="navbar-bira d-flex align-items-center justify-content-between">
    <a class="navbar-brand" href="{{ route('pagrindinis') }}">
        <i class="bi bi-kanban me-1"></i> Bira
    </a>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('pagrindinis') }}" class="nav-link-btn">
            <i class="bi bi-house me-1"></i>Pradžia
        </a>
        <a href="{{ route('boards.index') }}" class="nav-link-btn">
            <i class="bi bi-layout-three-columns me-1"></i>Lentos
        </a>
        <form action="{{ route('atsijungti') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="nav-link-btn" style="background:rgba(239,68,68,0.15);color:#fca5a5;">
                <i class="bi bi-box-arrow-right me-1"></i>Atsijungti
            </button>
        </form>
    </div>
</nav>

{{-- ── HERO ── --}}
<div class="profile-hero">
    <div class="container">

        @if(session('success'))
            <div class="alert-success-custom mb-4">
                <i class="bi bi-check-circle-fill"></i>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <div class="d-flex align-items-center gap-4 flex-wrap">
            {{-- Avataro apskritimas su inicialais --}}
            <div class="avatar-circle">
                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: '', 1, 1)) }}
            </div>
            <div>
                <h1>{{ $user->name }}</h1>
                <p class="mb-2 opacity-75" style="font-size:0.95rem;">{{ $user->email }}</p>
                <span class="badge-role">
                    <i class="bi bi-shield-check"></i>
                    {{ $role ? $role->name : 'Nežinoma rolė' }}
                </span>
                @if(!$user->is_active)
                    <span class="badge-role ms-2" style="background:rgba(239,68,68,0.3)">
                        <i class="bi bi-x-circle"></i> Neaktyvus
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── TURINYS ── --}}
<div class="container pb-5" style="margin-top: -2.5rem; position: relative; z-index: 10;">

    {{-- Statistika --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-plus-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $sukurtuUzduociu }}</div>
                    <div class="stat-label">Sukurtos užduotys</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-person-lines-fill"></i></div>
                <div>
                    <div class="stat-value">{{ $priskirtuUzduociu }}</div>
                    <div class="stat-label">Priskirtos užduotys</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $atliktaUzduociu }}</div>
                    <div class="stat-label">Atliktos užduotys</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Kairė kolona: Profilio info + komandos + veiksmai ── --}}
        <div class="col-12 col-lg-4 d-flex flex-column gap-4">

            {{-- Asmeninė informacija --}}
            <div class="info-card">
                <div class="section-title">
                    <i class="bi bi-person-badge"></i> Paskyros informacija
                </div>

                <div class="info-row">
                    <i class="bi bi-person info-icon"></i>
                    <div>
                        <div class="info-label">Vardas</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <i class="bi bi-envelope info-icon"></i>
                    <div>
                        <div class="info-label">El. paštas</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <i class="bi bi-shield info-icon"></i>
                    <div>
                        <div class="info-label">Sistemos rolė</div>
                        <div class="info-value">{{ $role ? $role->name : '—' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <i class="bi bi-toggle-on info-icon"></i>
                    <div>
                        <div class="info-label">Paskyros būsena</div>
                        <div class="info-value">
                            @if($user->is_active)
                                <span style="color:#16a34a; font-weight:600;">
                                    <i class="bi bi-circle-fill" style="font-size:0.5rem; vertical-align:middle;"></i> Aktyvi
                                </span>
                            @else
                                <span style="color:#dc2626; font-weight:600;">
                                    <i class="bi bi-circle-fill" style="font-size:0.5rem; vertical-align:middle;"></i> Neaktyvi
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <i class="bi bi-calendar-plus info-icon"></i>
                    <div>
                        <div class="info-label">Registracijos data</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Komandos --}}
            <div class="info-card">
                <div class="section-title">
                    <i class="bi bi-people"></i> Mano komandos
                </div>

                @if($teams->isNotEmpty())
                    @foreach($teams as $team)
                        <div class="info-row" style="border-bottom: 1px solid #f1f5f9;">
                            <i class="bi bi-diagram-3 info-icon"></i>
                            <div>
                                <div class="info-value">{{ $team->name }}</div>
                                @if($team->role_in_team)
                                    <div class="info-label">{{ $team->role_in_team }}</div>
                                @endif
                                @if($team->description)
                                    <div class="info-label">{{ $team->description }}</div>
                                @endif
                                <div class="info-label" style="margin-top:0.15rem;">
                                    <i class="bi bi-calendar2 me-1"></i>
                                    Prisijungė: {{ \Carbon\Carbon::parse($team->joined_at)->format('Y-m-d') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>Nesate jokios komandos narys.
                    </p>
                @endif
            </div>

            {{-- Veiksmai --}}
            <div class="info-card">
                <div class="section-title">
                    <i class="bi bi-gear"></i> Paskyros valdymas
                </div>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('profilis.redaguoti') }}" class="action-btn action-btn-primary">
                        <i class="bi bi-pencil-square"></i> Redaguoti profilį
                    </a>
                    <a href="{{ route('profilis.slaptazodis') }}" class="action-btn action-btn-outline">
                        <i class="bi bi-key"></i> Keisti slaptažodį
                    </a>
                </div>
            </div>

        </div>

        {{-- ── Dešinė kolona: paskutinės užduotys ── --}}
        <div class="col-12 col-lg-8">
            <div class="info-card" style="height: auto;">
                <div class="section-title">
                    <i class="bi bi-clock-history"></i> Paskutiniai veiksmai (užduotys)
                </div>

                @if($paskutinesUzduotys->isNotEmpty())
                    @foreach($paskutinesUzduotys as $task)
                        <div class="task-row">
                            {{-- Prioriteto taškas --}}
                            @php
                                $pdot = match(mb_strtolower($task->prioritetas ?? '')) {
                                    'skubus'    => 'p-skubus',
                                    'aukštas'   => 'p-aukštas',
                                    'vidutinis' => 'p-vidutinis',
                                    'žemas'     => 'p-žemas',
                                    default     => 'p-none',
                                };
                            @endphp
                            <span class="priority-dot {{ $pdot }}" title="Prioritetas: {{ $task->prioritetas ?? 'nenustatytas' }}"></span>

                            {{-- Tipas --}}
                            <span class="task-type-badge">{{ $task->tipas }}</span>

                            {{-- Pavadinimas --}}
                            <div class="flex-grow-1 text-truncate">
                                <span style="font-size:0.92rem; font-weight:500;">{{ $task->title }}</span>
                            </div>

                            {{-- Statusas --}}
                            <span class="status-pill {{ $task->is_done ? 'status-done' : 'status-pending' }}">
                                @if($task->is_done)
                                    <i class="bi bi-check2 me-1"></i>
                                @else
                                    <i class="bi bi-hourglass-split me-1"></i>
                                @endif
                                {{ $task->statusas }}
                            </span>

                            {{-- Data --}}
                            <span class="text-muted" style="font-size:0.78rem; white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($task->updated_at)->format('m-d H:i') }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size:2.5rem; color:#c4b5fd;"></i>
                        <p class="text-muted mt-2 mb-0">Kol kas nėra jokių užduočių.</p>
                    </div>
                @endif

                <div class="mt-3 text-end">
                    <a href="{{ route('boards.index') }}" class="action-btn action-btn-outline" style="display:inline-flex;">
                        <i class="bi bi-layout-three-columns"></i> Peržiūrėti visas lentas
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
