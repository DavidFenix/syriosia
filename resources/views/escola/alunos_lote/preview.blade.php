@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">🔎 Pré-visualização da Importação de Alunos</h2>

    <div class="alert alert-info">
        <p class="mb-1">
            <strong>Legenda:</strong>
        </p>
        <ul class="mb-0">
            <li><span class="badge bg-success">OK</span> Linhas válidas e prontas para importação (podem criar/vincular/enturmar ou mover turma).</li>
            <li><span class="badge bg-danger">Erro</span> Linhas com problemas — não serão importadas.</li>
            <li><span class="badge bg-warning text-dark">Aviso</span> Linhas informativas (por exemplo, aluno já enturmado na mesma turma).</li>
        </ul>
    </div>

    <div class="mb-3">
        <a href="{{ route('escola.alunos.lote.index') }}" class="btn btn-outline-secondary btn-sm">
            ⬅️ Voltar e enviar outro arquivo
        </a>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th># Linha</th>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Turma ID</th>
                    <th>Série/Turma</th>
                    <th>Turno</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($linhas as $linha)
                @php
                    $rowClass = match($linha['status']) {
                        'erro'  => 'table-danger',
                        'aviso' => 'table-warning',
                        default => 'table-success',
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $linha['linha'] }}</td>
                    <td>{{ $linha['matricula'] }}</td>
                    <td>{{ $linha['nome'] }}</td>
                    <td>{{ $linha['turma_id'] }}</td>
                    <td>{{ $linha['serie_turma'] }}</td>
                    <td>{{ $linha['turno'] }}</td>
                    <td>
                        @if($linha['status'] === 'erro')
                            <span class="badge bg-danger">Erro</span>
                        @elseif($linha['status'] === 'aviso')
                            <span class="badge bg-warning text-dark">Aviso</span>
                        @else
                            <span class="badge bg-success">OK</span>
                        @endif
                    </td>
                    <td>{{ $linha['msg'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        Nenhuma linha válida encontrada no arquivo.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @php
        $temImportavel = collect($linhas)->contains(fn($l) => !empty($l['importavel']) && $l['status'] !== 'erro');
    @endphp

    @if($temImportavel)
        <div class="alert alert-warning">
            Confirme abaixo para realizar a importação das linhas marcadas com
            <span class="badge bg-success">OK</span> ou <span class="badge bg-warning text-dark">Aviso</span>.
            As linhas com erro serão automaticamente ignoradas.
        </div>

        <form action="{{ route('escola.alunos.lote.importar') }}" method="POST">
            @csrf
            <input type="hidden" name="linhas" value="{{ $payload }}">
            <button type="submit" class="btn btn-primary">
                ✅ Confirmar Importação
            </button>
        </form>
    @else
        <div class="alert alert-danger">
            Não há linhas válidas para importar. Verifique o arquivo e tente novamente.
        </div>
    @endif
</div>
@endsection


{{--
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">🔎 Pré-visualização da Importação de Alunos</h2>

    <div class="alert alert-info">
        <p class="mb-1">
            <strong>Legenda:</strong>
        </p>
        <ul class="mb-0">
            <li><span class="badge bg-success">OK</span> Linhas válidas e prontas para importação.</li>
            <li><span class="badge bg-danger">Erro</span> Linhas com problemas — não serão importadas.</li>
            <li><span class="badge bg-warning text-dark">Aviso</span> Linhas informativas / ignoradas.</li>
        </ul>
    </div>

    <div class="mb-3">
        <a href="{{ route('escola.alunos.lote.index') }}" class="btn btn-outline-secondary btn-sm">
            ⬅️ Voltar e enviar outro arquivo
        </a>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th># Linha</th>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Turma ID</th>
                    <th>Série/Turma</th>
                    <th>Turno</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($linhas as $linha)
                @php
                    $rowClass = match($linha['status']) {
                        'erro'  => 'table-danger',
                        'aviso' => 'table-warning',
                        default => 'table-success',
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $linha['linha'] }}</td>
                    <td>{{ $linha['matricula'] }}</td>
                    <td>{{ $linha['nome'] }}</td>
                    <td>{{ $linha['turma_id'] }}</td>
                    <td>{{ $linha['serie_turma'] }}</td>
                    <td>{{ $linha['turno'] }}</td>
                    <td>
                        @if($linha['status'] === 'erro')
                            <span class="badge bg-danger">Erro</span>
                        @elseif($linha['status'] === 'aviso')
                            <span class="badge bg-warning text-dark">Aviso</span>
                        @else
                            <span class="badge bg-success">OK</span>
                        @endif
                    </td>
                    <td>{{ $linha['msg'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        Nenhuma linha válida encontrada no arquivo.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @php
        $temImportavel = collect($linhas)->contains(fn($l) => !empty($l['importavel']) && $l['status'] !== 'erro');
    @endphp

    @if($temImportavel)
        <div class="alert alert-warning">
            Confirme abaixo para realizar a importação das linhas em <span class="badge bg-success">OK</span>.
            As linhas com erro serão automaticamente ignoradas.
        </div>

        <form action="{{ route('escola.alunos.lote.importar') }}" method="POST">
            @csrf
            <input type="hidden" name="linhas" value="{{ $payload }}">
            <button type="submit" class="btn btn-primary">
                ✅ Confirmar Importação
            </button>
        </form>
    @else
        <div class="alert alert-danger">
            Não há linhas válidas para importar. Verifique o arquivo e tente novamente.
        </div>
    @endif
</div>
@endsection
--}}