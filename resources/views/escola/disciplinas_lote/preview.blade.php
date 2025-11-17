@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">🔎 Pré-visualização do arquivo</h2>

    <a href="{{ route('escola.disciplinas.lote.index') }}" class="btn btn-outline-secondary mb-3">
        ⬅️ Enviar outro arquivo
    </a>

    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>Linha</th>
                <th>Abrev.</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Mensagem</th>
            </tr>
        </thead>
        <tbody>
        @foreach($linhas as $l)
            @php
                $class = match($l['status']) {
                    'erro'  => 'table-danger',
                    'aviso' => 'table-warning',
                    default => 'table-success',
                };
            @endphp
            <tr class="{{ $class }}">
                <td>{{ $l['linha'] }}</td>
                <td>{{ $l['abr'] }}</td>
                <td>{{ $l['descr_d'] }}</td>
                <td>{{ ucfirst($l['status']) }}</td>
                <td>{{ $l['msg'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @php
        $temImportavel = collect($linhas)->contains(fn($l) => $l['importavel'] === true);
    @endphp

    @if($temImportavel)
        <form method="POST" action="{{ route('escola.disciplinas.lote.importar') }}">
            @csrf
            <input type="hidden" name="linhas" value="{{ $payload }}">
            <button class="btn btn-primary">✅ Importar Disciplinas</button>
        </form>
    @else
        <div class="alert alert-danger mt-3">
            Não há linhas válidas para importação.
        </div>
    @endif

</div>
@endsection
