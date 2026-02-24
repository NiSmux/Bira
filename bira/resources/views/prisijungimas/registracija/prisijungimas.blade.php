<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Prisijungimas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto shadow" style="max-width: 400px; padding: 20px;">
        <h2 class="text-center">Prisijungti</h2>
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if($errors->has('login_error')) <div class="alert alert-danger">{{ $errors->first('login_error') }}</div> @endif

        <form method="POST" action="{{ route('prisijungimas.jungtis') }}">
            @csrf
            <div class="mb-3">
                <label>El. paštas:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Slaptažodis:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Prisijungti</button>
            <p>Neturite paskyros? <a href="{{ route('registracija.forma') }}">Registruotis</a></p>
        </form>
    </div>
</div>
</body>
</html>