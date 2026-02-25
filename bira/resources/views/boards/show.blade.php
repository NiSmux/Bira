<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Lenta: {{ $board->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .kanban-column {
            min-width: 300px;
            max-width: 350px;
            background-color: #ebedf0;
            border-radius: 8px;
            padding: 10px;
            margin-right: 15px;
            display: inline-block;
            vertical-align: top;
            height: fit-content;
            min-height: 500px;
        }
        .kanban-container {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('boards.index') }}">Bira Kanban</a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3 text-white">
                    Lenta: <strong>{{ $board->name }}</strong>
                </span>
                <a href="{{ route('pagrindinis') }}" class="btn btn-outline-light btn-sm me-2">Pradžia</a>
                <a href="{{ route('boards.index') }}" class="btn btn-outline-light btn-sm">Mano lentos</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">{{ $board->name }}</h2>
            <a href="{{ route('boards.tasks.createTask', $board->id) }}" class="btn btn-success">+ Nauja užduotis</a>
        </div>

        <div class="kanban-container h-100">
            @forelse($statuses as $status)
                <div class="kanban-column shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase fw-bold text-secondary mb-0">{{ $status->name }}</h6>
                        <span class="badge bg-secondary rounded-pill">0</span>
                    </div>
                    <div class="kanban-tasks" style="min-height: 400px;">
                        <!-- Užduotys bus čia -->
                        @foreach($board->items->where('status_id', $status->id) as $item)
                            <div class="card mb-2 shadow-sm">
                                <div class="card-body p-2">
                                    <strong>{{ $item->title }}</strong>
                                    <div class="small text-muted">
                                        {{ $item->description }}
                                    </div>
                                </div>
                                <form action="{{ route('boards.tasks.destroy', [$board->id, $item->id]) }}"
                                    method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('Ar tikrai norite ištrinti?')">
                                    @csrf
                                    @method('DELETE')
                                    
                                    <a href="{{ route('boards.tasks.edit', [$board->id, $item->id]) }}"
                                    class="btn btn-sm btn-warning mt-2">
                                        ✏ Redaguoti
                                    </a>

                                    <button class="btn btn-sm btn-danger mt-2">
                                        🗑 Ištrinti
                                    </button>
                                </form>
                            </div>
                        @endforeach

                        @if($board->items->where('status_id', $status->id)->count() == 0)
                            <div class="text-center text-muted small mt-5">
                                Nėra užduočių
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-warning">
                    Šiai lentai nėra sukonfigūruota jokia eiga (workflow statuses).
                </div>
            @endforelse
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
