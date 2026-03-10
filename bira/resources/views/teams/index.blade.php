<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Komandos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Komandos</h1>
            <a href="{{ route('teams.create') }}" class="btn btn-primary">Sukurti komandą</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <h3 class="mb-3">Mano sukurtos komandos</h3>
        <div class="row mb-5">
            @forelse($ownedTeams as $team)
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $team->name }}</h5>
                            <p class="text-muted">{{ $team->description ?: 'Aprašymo nėra' }}</p>
                            <p class="mb-3">Narių skaičius: {{ $team->members->count() }}</p>
                            <a href="{{ route('teams.show', $team->id) }}" class="btn btn-outline-primary">Atidaryti</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">Dar nesukūrėte nei vienos komandos.</div>
                </div>
            @endforelse
        </div>

        <h3 class="mb-3">Komandos, kuriose esu narys</h3>
        <div class="row">
            @forelse($memberTeams as $team)
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $team->name }}</h5>
                            <p class="text-muted">{{ $team->description ?: 'Aprašymo nėra' }}</p>
                            <p class="mb-0">Narių skaičius: {{ $team->members->count() }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-secondary">Kol kas nesate kitų komandų narys.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            <a href="{{ route('pagrindinis') }}" class="btn btn-link text-secondary ps-0">Grįžti į pradžią</a>
        </div>
    </div>
</body>
</html>