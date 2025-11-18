@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">🔎 Pré-visualização das Ofertas</h2>

    <div class="alert alert-info">
        <strong>Legenda:</strong>
        <ul class="mb-0">
            <li><span class="badge bg-success">OK</span> → linha válida, será importada</li>
            <li><span class="badge bg-warning text-dark">Aviso</span> → linha informativa, não será importada</li>
            <li><span class="badge bg-danger">Erro</span> → linha inválida, não será importada</li>
        </ul>
    </div>

    <a href="{{ route('escola.ofertas.lote.index') }}" class="btn btn-outline-secondary btn-sm mt-2">
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
                @forelse($linhas as $l)
                    @php
                        $rowClass = match($l['status']) {
                            'erro'  => 'table-danger',
                            'aviso' => 'table-warning',
                            default => 'table-success',
                        };
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $l['linha'] }}</td>
                        <td>{{ $l['cpf'] }}</td>
                        <td>{{ $l['nome'] }}</td>
                        <td>{{ $l['disciplina_id'] }}</td>
                        <td>{{ $l['descr_d'] }}</td>
                        <td>{{ $l['turma_id'] }}</td>
                        <td>{{ $l['serie_turma'] }}</td>
                        <td>
                            @if($l['status'] === 'erro')
                                <span class="badge bg-danger">Erro</span>
                            @elseif($l['status'] === 'aviso')
                                <span class="badge bg-warning text-dark">Aviso</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                        <td>{{ $l['msg'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            Nenhuma linha encontrada no arquivo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php
        // Usa a flag importavel em vez de só status
        $temImportavel = collect($linhas)->contains(fn($l) => !empty($l['importavel']) && $l['status'] !== 'erro');
    @endphp

    @if($temImportavel)
        <div class="alert alert-warning mt-3">
            As linhas marcadas como <span class="badge bg-success">OK</span> serão importadas.
            Avisos e erros serão ignorados.
        </div>

        <form action="{{ route('escola.ofertas.lote.importar') }}" method="POST">
            @csrf
            <input type="hidden" name="linhas" value="{{ $payload }}">

            <button class="btn btn-primary mt-2">
                ✅ Confirmar Importação
            </button>
        </form>
    @else
        <div class="alert alert-danger mt-3">
            Não há linhas válidas para importação.
        </div>
    @endif

</div>
@endsection
