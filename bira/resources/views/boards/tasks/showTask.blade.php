<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>{{ $task->title }} - Bira Kanban</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .task-detail-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .task-label {
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
        .task-value {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .badge-points {
            background-color: #0dcaf0;
            color: #000;
            font-weight: bold;
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('boards.index') }}">Bira Kanban</a>
            <div class="d-flex align-items-center">
                <a href="{{ route('boards.show', $board->id) }}" class="btn btn-outline-light btn-sm">Atgal į lentą</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="task-detail-container">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <h1 class="h2 mb-0">{{ $task->title }}</h1>
                @if($task->story_points)
                    <span class="badge badge-points">SP: {{ $task->story_points }}</span>
                @endif
            </div>

            <hr class="mb-4">

            <div class="row">
                <div class="col-md-8">
                    <div class="task-label">Aprašymas</div>
                    <div class="task-value text-break">
                        @if($task->description)
                            {!! nl2br(e($task->description)) !!}
                        @else
                            <span class="text-muted italic">Aprašymo nėra</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 border-start">
                    <div class="task-label">Statusas</div>
                    <div class="task-value">
                        <span class="badge bg-secondary p-2">{{ $task->status->name ?? 'Nėra' }}</span>
                    </div>

                    <div class="task-label">Tipas</div>
                    <div class="task-value">
                        {{ $task->type->name ?? 'Nėra' }}
                    </div>

                    <div class="task-label">Prioritetas</div>
                    <div class="task-value">
                        {{ $task->priority->name ?? 'Nėra' }}
                    </div>

                    <div class="task-label">Sukurta</div>
                    <div class="task-value small">
                        {{ $task->created_at ? $task->created_at->format('Y-m-d H:i') : 'Nežinoma' }}
                        <br>
                        <span class="text-muted">Autorius: {{ $task->creator->name ?? 'Sistemos vartotojas' }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <a href="{{ route('boards.tasks.edit', [$board->id, $task->id]) }}" class="btn btn-warning me-2">✏ Redaguoti</a>
                <form action="{{ route('boards.tasks.destroy', [$board->id, $task->id]) }}" method="POST" onsubmit="return confirm('Ar tikrai norite ištrinti?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">🗑 Ištrinti</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
