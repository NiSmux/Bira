<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Lenta: {{ $board->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('boards.index') }}">Bira Kanban</a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Lenta: <strong>{{ $board->name }}</strong>
                </span>
                <a href="{{ route('pagrindinis') }}" class="btn btn-outline-light btn-sm">Pradžia</a>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">{{ $board->name }}</h2>
                        <a href="{{ route('uzduotis.prideti', $board->id) }}" class="btn btn-sm btn-success">+ Nauja užduotis</a>
                    </div>
                    <div class="card-body bg-light" style="min-height: 400px;">
                        <p class="text-center text-muted mt-5">
                            Čia bus jūsų Kanban stulpeliai ir užduotys.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="{{ route('boards.index') }}" class="btn btn-outline-secondary">Grįžti į sąrašą</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
