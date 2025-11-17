@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">📊 Resultado da Importação</h2>

    <a href="{{ route('escola.disciplinas.lote.index') }}" class="btn btn-outline-secondary mb-3">
        ⬅️ Importar outro arquivo
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
        @foreach($resultado as $r)
            @php
                $class = match($r['status']) {
                    'sucesso' => 'table-success',
                    'aviso'   => 'table-warning',
                    'erro'    => 'table-danger',
                    default   => 'table-secondary',
                };
            @endphp
            <tr class="{{ $class }}">
                <td>{{ $r['linha'] }}</td>
                <td>{{ $r['abr'] }}</td>
                <td>{{ $r['descr_d'] }}</td>
                <td>{{ ucfirst($r['status']) }}</td>
                <td>{{ $r['msg'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection
