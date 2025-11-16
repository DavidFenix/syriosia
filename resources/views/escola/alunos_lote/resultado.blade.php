@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">📊 Resultado da Importação de Alunos</h2>

    <div class="mb-3">
        <a href="{{ route('escola.alunos.lote.index') }}" class="btn btn-outline-secondary btn-sm">
            ⬅️ Voltar para Importar Outro Arquivo
        </a>
        <a href="{{ route('escola.alunos.index') }}" class="btn btn-outline-primary btn-sm">
            👀 Ver lista de alunos
        </a>
    </div>

    @php
        $totalSucesso = collect($resultado)->where('status', 'sucesso')->count();
        $totalAviso   = collect($resultado)->where('status', 'aviso')->count();
        $totalErro    = collect($resultado)->where('status', 'erro')->count();
        $totalIgn     = collect($resultado)->where('status', 'ignorado')->count();
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-success">Sucesso</div>
                    <div class="fs-4 fw-bold">{{ $totalSucesso }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-warning">Avisos</div>
                    <div class="fs-4 fw-bold">{{ $totalAviso }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-danger">Erros</div>
                    <div class="fs-4 fw-bold">{{ $totalErro }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-muted">Ignorados</div>
                    <div class="fs-4 fw-bold">{{ $totalIgn }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th># Linha</th>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($resultado as $r)
                @php
                    $rowClass = match($r['status']) {
                        'sucesso'  => 'table-success',
                        'aviso'    => 'table-warning',
                        'erro'     => 'table-danger',
                        default    => 'table-secondary',
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $r['linha'] ?? '-' }}</td>
                    <td>{{ $r['matricula'] ?? '' }}</td>
                    <td>{{ $r['nome'] ?? '' }}</td>
                    <td class="text-nowrap">
                        @if($r['status'] === 'sucesso')
                            <span class="badge bg-success">Sucesso</span>
                        @elseif($r['status'] === 'aviso')
                            <span class="badge bg-warning text-dark">Aviso</span>
                        @elseif($r['status'] === 'erro')
                            <span class="badge bg-danger">Erro</span>
                        @else
                            <span class="badge bg-secondary">Ignorado</span>
                        @endif
                    </td>
                    <td>{{ $r['msg'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Nenhuma linha processada.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


{{--
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">📊 Resultado da Importação de Alunos</h2>

    <div class="mb-3">
        <a href="{{ route('escola.alunos.lote.index') }}" class="btn btn-outline-secondary btn-sm">
            ⬅️ Voltar para Importar Outro Arquivo
        </a>
        <a href="{{ route('escola.alunos.index') }}" class="btn btn-outline-primary btn-sm">
            👀 Ver lista de alunos
        </a>
    </div>

    @php
        $totalSucesso = collect($resultado)->where('status', 'sucesso')->count();
        $totalAviso   = collect($resultado)->where('status', 'aviso')->count();
        $totalErro    = collect($resultado)->where('status', 'erro')->count();
        $totalIgn     = collect($resultado)->where('status', 'ignorado')->count();
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-success">Sucesso</div>
                    <div class="fs-4 fw-bold">{{ $totalSucesso }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-warning">Avisos</div>
                    <div class="fs-4 fw-bold">{{ $totalAviso }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-danger">Erros</div>
                    <div class="fs-4 fw-bold">{{ $totalErro }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fw-semibold text-muted">Ignorados</div>
                    <div class="fs-4 fw-bold">{{ $totalIgn }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th># Linha</th>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($resultado as $r)
                @php
                    $rowClass = match($r['status']) {
                        'sucesso'  => 'table-success',
                        'aviso'    => 'table-warning',
                        'erro'     => 'table-danger',
                        default    => 'table-secondary',
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $r['linha'] ?? '-' }}</td>
                    <td>{{ $r['matricula'] ?? '' }}</td>
                    <td>{{ $r['nome'] ?? '' }}</td>
                    <td class="text-nowrap">
                        @if($r['status'] === 'sucesso')
                            <span class="badge bg-success">Sucesso</span>
                        @elseif($r['status'] === 'aviso')
                            <span class="badge bg-warning text-dark">Aviso</span>
                        @elseif($r['status'] === 'erro')
                            <span class="badge bg-danger">Erro</span>
                        @else
                            <span class="badge bg-secondary">Ignorado</span>
                        @endif
                    </td>
                    <td>{{ $r['msg'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Nenhuma linha processada.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
--}}