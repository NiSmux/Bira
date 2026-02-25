<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Nauja užduotis - {{ $board->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('boards.index') }}">Bira Kanban</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 text-white">
                Lenta: <strong>{{ $board->name }}</strong>
            </span>
            <a href="{{ route('boards.show', $board->id) }}" class="btn btn-outline-light btn-sm">
                ← Grįžti į lentą
            </a>
        </div>
    </div>
</nav>

<div class="container" style="max-width: 700px;">

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-4">Nauja užduotis</h4>

            {{-- Klaidos --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('boards.tasks.store', $board->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Užduoties pavadinimas</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title') }}"
                           required
                           placeholder="Įveskite pavadinimą...">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Aprašymas</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3"
                              placeholder="Trumpas užduoties aprašymas...">{{ old('description') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipas</label>
                        <select name="item_type_id" class="form-select" required>
                            <option disabled selected>Pasirinkite tipą...</option>
                            @foreach($itemTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('item_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Prioritetas</label>
                        <select name="priority_id" class="form-select">
                            <option value="">-- Nėra prioriteto --</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}"
                                    {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                    {{ $priority->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Statusas (Stulpelis)</label>
                    <select name="status_id" class="form-select" required>
                        <option disabled selected>Pasirinkite stulpelį...</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}"
                                {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('boards.show', $board->id) }}" class="btn btn-secondary">
                        Atšaukti
                    </a>

                    <button type="submit" class="btn btn-success px-4">
                        Išsaugoti
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>