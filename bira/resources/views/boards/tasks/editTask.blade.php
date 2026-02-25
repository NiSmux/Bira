<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Redaguoti užduotį</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 700px;">

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-4">Redaguoti užduotį</h4>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('boards.tasks.update', [$board->id, $task->id]) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Pavadinimas</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title', $task->title) }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Aprašymas</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3">{{ old('description', $task->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Statusas</label>
                    <select name="status_id" class="form-select" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}"
                                {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tipas</label>
                    <select name="item_type_id" class="form-select" required>
                        @foreach($itemTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ old('item_type_id', $task->item_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Prioritetas</label>
                    <select name="priority_id" class="form-select">
                        <option value="">-- Nėra prioriteto --</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}"
                                {{ old('priority_id', $task->priority_id) == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('boards.show', $board->id) }}"
                       class="btn btn-secondary">
                        Atšaukti
                    </a>

                    <button type="submit"
                            class="btn btn-primary px-4">
                        Atnaujinti
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>