@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">🔎 Pré-visualização das Ofertas</h2>

    <div class="alert alert-info">
        <strong>Legenda:</strong>
        <ul class="mb-0">
            <li><span class="badge bg-success">OK</span> → linha válida, será importada</li>
            <li><span class="badge bg-danger">Erro</span> → linha inválida, será ignorada</li>
        </ul>
    </div>

    <a href="{{ route('escola.ofertas.lote.index') }}" class="btn btn-outline-secondary btn-sm">
        ⬅️ Enviar outro arquivo
    </a>

    <div class="table-responsive mt-3">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>CPF Professor</th>
                    <th>Nome Professor</th>
                    <th>Disciplina ID</th>
                    <th>Disciplina</th>
                    <th>Turma ID</th>
                    <th>Série/Turma</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>

            <tbody>
                @foreach($linhas as $l)
                    @php
                        $rowClass = $l['status'] === 'erro' ? 'table-danger' : 'table-success';
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $l['linha'] }}</td>
                        <td>{{ $l['cpf'] }}</td>
                        <td>{{ $l['professor'] }}</td>
                        <td>{{ $l['disciplina_id'] }}</td>
                        <td>{{ $l['disciplina'] }}</td>
                        <td>{{ $l['turma_id'] }}</td>
                        <td>{{ $l['serie_turma'] }}</td>
                        <td>
                            @if($l['status'] === 'erro')
                                <span class="badge bg-danger">Erro</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                        <td>{{ $l['msg'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        $temImportavel = collect($linhas)->contains(fn($l) => $l['status'] === 'ok');
    @endphp

    @if($temImportavel)
        <form action="{{ route('escola.ofertas.lote.importar') }}" method="POST">
            @csrf
            <input type="hidden" name="linhas" value="{{ $payload }}">

            <button class="btn btn-primary mt-3">
                ✅ Confirmar Importação
            </button>
        </form>
    @else
        <div class="alert alert-danger mt-3">
            Nenhuma linha válida para importação.
        </div>
    @endif

</div>
@endsection
