<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>{{ $team->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">{{ $team->name }}</h1>
                <p class="text-muted mb-0">{{ $team->description ?: 'Aprašymo nėra' }}</p>
            </div>
            <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary">Atgal</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-md-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Pridėti narį</h5>
                    </div>
                    <div class="card-body">
                        @if($availableUsers->isEmpty())
                            <p class="text-muted mb-0">Nebėra vartotojų, kuriuos galima pridėti.</p>
                        @else
                            <form action="{{ route('teams.members.store', $team->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Vartotojas</label>
                                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                        <option value="">Pasirinkite vartotoją</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Pridėti</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Komandos lentos</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($team->boards as $board)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $board->name }}</span>
                                <a href="{{ route('boards.show', $board->id) }}" class="btn btn-sm btn-outline-primary">Atidaryti</a>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Komanda dar neturi lentų.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Komandos nariai</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Vardas</th>
                                    <th>El. paštas</th>
                                    <th>Rolė</th>
                                    <th class="text-end">Veiksmai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($team->members as $member)
                                    <tr>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->pivot->role_in_team }}</td>
                                        <td class="text-end">
                                            @if($member->pivot->role_in_team !== 'owner')
                                                <form action="{{ route('teams.members.destroy', [$team->id, $member->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Ar tikrai norite pašalinti šį narį?')">
                                                        Išmesti
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-secondary">Owner</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>