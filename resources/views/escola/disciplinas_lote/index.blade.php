@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">📥 Importar Disciplinas em Lote</h2>

    <p class="text-muted">
        Envie um arquivo CSV para criar ou atualizar disciplinas desta escola.
    </p>

    <a href="{{ route('escola.disciplinas.lote.modelo') }}" class="btn btn-outline-primary mb-3">
        ⬇️ Baixar modelo CSV
    </a>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Enviar arquivo</div>
        <div class="card-body">
            <form action="{{ route('escola.disciplinas.lote.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Arquivo CSV</label>
                    <input type="file" name="arquivo" class="form-control" required>
                </div>

                <button class="btn btn-success">🔎 Pré-visualizar</button>
            </form>
        </div>
    </div>

    @if($disciplinas->count())
        <h5 class="fw-semibold">📚 Disciplinas atuais</h5>
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Abrev.</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disciplinas as $d)
                <tr>
                    <td>{{ $d->abr }}</td>
                    <td>{{ $d->descr_d }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection
