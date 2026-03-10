<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Mano lentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Mano Kanban lentos</h1>
            <a href="{{ route('boards.create') }}" class="btn btn-primary">Sukurti naują lentą</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            @forelse($boards as $board)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $board->name }}</h5>
                            <p class="mb-1"><strong>Komanda:</strong> {{ $board->team?->name ?? 'Nėra' }}</p>
                            <p class="card-text text-muted small">Sukurta: {{ $board->created_at ?? 'N/A' }}</p>
                            <a href="{{ route('boards.show', $board->id) }}" class="btn btn-outline-primary">Atidaryti lentą</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Jūs dar neturite jokių lentų. <a href="{{ route('boards.create') }}">Sukurkite pirmąją!</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            <a href="{{ route('pagrindinis') }}" class="btn btn-link text-secondary ps-0">Grįžti į pradžią</a>
        </div>
    </div>
</body>
</html>