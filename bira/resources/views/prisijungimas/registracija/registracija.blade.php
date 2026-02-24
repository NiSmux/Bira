<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Registracija</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto shadow" style="max-width: 500px; padding: 20px;">
        <h2 class="text-center">Registracija</h2>
        
        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><small>{{ $error }}</small></li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('registracija.registruotis') }}">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Vardas:*</label>
                <input type="text" name="vardas" 
                       class="form-control @error('vardas') is-invalid @enderror" 
                       value="{{ old('vardas') }}" required>
                @error('vardas') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">El. paštas:*</label>
                <input type="email" name="e_pastas" 
                       class="form-control @error('e_pastas') is-invalid @enderror" 
                       value="{{ old('e_pastas') }}" required>
                @error('e_pastas') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Slaptažodis:*</label>
                <input type="password" name="slaptazodis" 
                       class="form-control @error('slaptazodis') is-invalid @enderror" required>
                @error('slaptazodis') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Pakartokite slaptažodį:*</label>
                <input type="password" name="slaptazodis_confirmation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Rolė:*</label>
                <select name="role" class="form-select" required>
                    <option value="1" {{ old('role') == 1 ? 'selected' : '' }}>Vartotojas</option>
                    <option value="2" {{ old('role') == 2 ? 'selected' : '' }}>Administratorius</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success w-100">Registruotis</button>
            
            <div class="mt-3 text-center">
                <small>Jau turite paskyrą? <a href="{{ route('login') }}">Prisijunkite</a></small>
            </div>
        </form>
    </div>
</div>
</body>
</html>