<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Pagrindinis puslapis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <div class="card p-5 shadow">
            <h1>Sveiki atvykę!</h1>
            
            @auth
                <p class="fs-4">Sveikas, <strong>{{ auth()->user()->name }}</strong>!</p>
                <p>Tavo el. paštas: {{ auth()->user()->email }}</p>
                
                <form action="{{ route('atsijungti') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Atsijungti</button>
                </form>
            @else
                <p class="fs-4">Jūs esate svečias.</p>
                <div class="mt-3">
                    <a href="{{ route('prisijungimas.forma') }}" class="btn btn-primary">Prisijungti</a>
                    <a href="{{ route('registracija.forma') }}" class="btn btn-outline-secondary">Registracija</a>
                </div>
            @endauth
        </div>
    </div>
</body>
</html>