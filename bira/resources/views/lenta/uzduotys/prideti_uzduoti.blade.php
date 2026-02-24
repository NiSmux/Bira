{{-- Galima pridėti klaidų pranešimus viršuje --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('uzduotis.saugoti') }}" method="POST" class="p-3 shadow-sm rounded bg-light">
    @csrf
    
    {{-- Paslėptas lentos ID, kad žinotume, kuriai lentai priklauso --}}
    <input type="hidden" name="board_id" value="{{ $board->id }}">

    <div class="mb-3">
        <label class="form-label font-weight-bold">Užduoties pavadinimas</label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Įveskite pavadinimą...">
    </div>

    <div class="mb-3">
        <label class="form-label font-weight-bold">Aprašymas</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Trumpas užduoties aprašymas...">{{ old('description') }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label font-weight-bold">Užduoties tipas</label>
            <select name="item_type_id" class="form-select" required>
                <option value="" selected disabled>Pasirinkite tipą...</option>
                @foreach($itemTypes as $type)
                    <option value="{{ $type->id }}" {{ old('item_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label font-weight-bold">Prioritetas</label>
            <select name="priority_id" class="form-select">
                <option value="" selected>-- Nėra prioriteto --</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                        {{ $priority->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label font-weight-bold">Statusas (Stulpelis)</label>
        <select name="status_id" class="form-select" required>
            <option value="" selected disabled>Pasirinkite stulpelį...</option>
            @foreach($statuses as $status)
                <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                    {{ $status->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('lenta.rodyti', $board->id) }}" class="btn btn-secondary">Atšaukti</a>
        <button type="submit" class="btn btn-success px-5">Išsaugoti užduotį</button>
    </div>
</form>