@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">📊 Resultado da Importação</h2>

    <a href="{{ route('escola.ofertas.lote.index') }}" class="btn btn-outline-secondary btn-sm">
        ⬅️ Importar outro arquivo
    </a>

    <div class="table-responsive mt-3">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>CPF</th>
                    <th>Professor</th>
                    <th>Disciplina</th>
                    <th>Turma</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>

            <tbody>
                @foreach($resultado as $r)
                    @php
                        $rowClass = match($r['status']) {
                            'sucesso' => 'table-success',
                            'erro'    => 'table-danger',
                            default   => 'table-warning'
                        };
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $r['linha'] }}</td>
                        <td>{{ $r['cpf'] }}</td>
                        <td>{{ $r['professor'] }}</td>
                        <td>{{ $r['disciplina'] }}</td>
                        <td>{{ $r['turma'] }}</td>

                        <td>
                            @if($r['status'] === 'sucesso')
                                <span class="badge bg-success">Sucesso</span>
                            @elseif($r['status'] === 'erro')
                                <span class="badge bg-danger">Erro</span>
                            @else
                                <span class="badge bg-warning text-dark">Aviso</span>
                            @endif
                        </td>

                        <td>{{ $r['msg'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
