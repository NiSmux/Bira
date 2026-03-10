<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keisti slaptažodį – Bira</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9ff;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .navbar-bira {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            padding: 0.75rem 1.5rem;
        }
        .navbar-bira .navbar-brand { font-weight: 700; font-size: 1.3rem; color: #fff !important; }
        .nav-link-btn {
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: background 0.2s, color 0.2s;
        }
        .nav-link-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }

        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(79,70,229,0.08);
            border: 1px solid #e8e6ff;
            max-width: 520px;
            width: 100%;
        }
        .form-card h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }
        .form-label { font-weight: 500; font-size: 0.875rem; color: #374151; }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        }
        .btn-save {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: opacity 0.2s, transform 0.2s;
        }
        .btn-save:hover { opacity: 0.9; transform: translateY(-1px); color:#fff; }
        .btn-cancel {
            background: transparent;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 1.2rem;
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-cancel:hover { background: #f1f5f9; color: #1e293b; }

        /* Slaptažodžio stiprumo indikatrorius */
        .strength-bar {
            height: 4px;
            border-radius: 10px;
            background: #e2e8f0;
            margin-top: 6px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }
    </style>
</head>
<body>

<nav class="navbar-bira d-flex align-items-center justify-content-between mb-5">
    <a class="navbar-brand" href="{{ route('pagrindinis') }}">
        <i class="bi bi-kanban me-1"></i> Bira
    </a>
    <div class="d-flex gap-2">
        <a href="{{ route('profilis.rodyti') }}" class="nav-link-btn">
            <i class="bi bi-arrow-left me-1"></i>Atgal į profilį
        </a>
    </div>
</nav>

<div class="d-flex justify-content-center px-3">
    <div class="form-card">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width:48px;height:48px;border-radius:14px;background:#fae8ff;display:flex;align-items:center;justify-content:center;color:#a21caf;font-size:1.4rem;">
                <i class="bi bi-key-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">Keisti slaptažodį</h2>
                <p class="text-muted mb-0" style="font-size:0.85rem;">Saugumas – pirmiausia</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-3" style="font-size:0.875rem;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profilis.slaptazodis.keisti') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="dabartinis_slaptazodis" class="form-label">
                    <i class="bi bi-lock me-1 text-primary"></i> Dabartinis slaptažodis
                </label>
                <input
                    type="password"
                    id="dabartinis_slaptazodis"
                    name="dabartinis_slaptazodis"
                    class="form-control @error('dabartinis_slaptazodis') is-invalid @enderror"
                    required
                    placeholder="Įveskite dabartinį slaptažodį"
                >
                @error('dabartinis_slaptazodis')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="naujas_slaptazodis" class="form-label">
                    <i class="bi bi-lock-fill me-1 text-primary"></i> Naujas slaptažodis
                </label>
                <input
                    type="password"
                    id="naujas_slaptazodis"
                    name="naujas_slaptazodis"
                    class="form-control @error('naujas_slaptazodis') is-invalid @enderror"
                    required
                    minlength="8"
                    placeholder="Mažiausiai 8 simboliai"
                    oninput="checkStrength(this.value)"
                >
                <div class="strength-bar mt-1">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <div id="strengthText" class="mt-1" style="font-size:0.75rem; color:#64748b;"></div>
                @error('naujas_slaptazodis')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="naujas_slaptazodis_confirmation" class="form-label">
                    <i class="bi bi-lock-fill me-1 text-primary"></i> Pakartokite naują slaptažodį
                </label>
                <input
                    type="password"
                    id="naujas_slaptazodis_confirmation"
                    name="naujas_slaptazodis_confirmation"
                    class="form-control"
                    required
                    placeholder="Pakartokite naują slaptažodį"
                >
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-save">
                    <i class="bi bi-check2 me-1"></i> Pakeisti
                </button>
                <a href="{{ route('profilis.rodyti') }}" class="btn-cancel">Atšaukti</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function checkStrength(value) {
        const fill = document.getElementById('strengthFill');
        const text = document.getElementById('strengthText');
        let score = 0;
        if (value.length >= 6)  score++;
        if (value.length >= 10) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const levels = [
            { pct: '0%',   color: '#e2e8f0', label: '' },
            { pct: '25%',  color: '#ef4444', label: 'Labai silpnas' },
            { pct: '50%',  color: '#f97316', label: 'Silpnas' },
            { pct: '75%',  color: '#eab308', label: 'Vidutinis' },
            { pct: '90%',  color: '#22c55e', label: 'Stiprus' },
            { pct: '100%', color: '#15803d', label: 'Labai stiprus' },
        ];
        const lvl = levels[score] || levels[0];
        fill.style.width = lvl.pct;
        fill.style.background = lvl.color;
        text.textContent = lvl.label;
        text.style.color = lvl.color;
    }
</script>
</body>
</html>
