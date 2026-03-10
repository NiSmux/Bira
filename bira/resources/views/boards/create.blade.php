<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Sukurti naują lentą</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Nauja Kanban lenta</h3>
                    </div>
                    <div class="card-body">
                        @if($teams->isEmpty())
                            <div class="alert alert-warning">
                                Pirmiausia turite susikurti komandą.
                            </div>
                            <a href="{{ route('teams.create') }}" class="btn btn-primary">Sukurti komandą</a>
                        @else
                            <form action="{{ route('boards.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">Lentos pavadinimas</label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="team_id" class="form-label">Komanda</label>
                                    <select name="team_id" id="team_id" class="form-select @error('team_id') is-invalid @enderror" required>
                                        <option value="">Pasirinkite komandą</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>
                                                {{ $team->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('team_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('pagrindinis') }}" class="btn btn-outline-secondary">Atšaukti</a>
                                    <button type="submit" class="btn btn-success">Sukurti</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>