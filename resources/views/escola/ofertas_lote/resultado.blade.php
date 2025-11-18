@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">📊 Resultado da Importação de Ofertas</h2>

    <a href="{{ route('escola.ofertas.lote.index') }}" class="btn btn-outline-secondary btn-sm">
        ⬅️ Importar outro arquivo
    </a>

    @php
        $totalSucesso = collect($resultado)->where('status', 'sucesso')->count();
        $totalAviso   = collect($resultado)->where('status', 'aviso')->count();
        $totalErro    = collect($resultado)->where('status', 'erro')->count();
        $totalIgn     = collect($resultado)->where('status', 'ignorado')->count();
    @endphp

    <div class="row g-2 my-3">
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

    <div class="table-responsive mt-2">
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
                @forelse($resultado as $r)
                    @php
                        $rowClass = match($r['status']) {
                            'sucesso'  => 'table-success',
                            'erro'     => 'table-danger',
                            'aviso'    => 'table-warning',
                            default    => 'table-secondary',
                        };
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $r['linha'] ?? '-' }}</td>
                        <td>{{ $r['cpf'] ?? '' }}</td>
                        <td>{{ $r['professor'] ?? '' }}</td>
                        <td>{{ $r['disciplina'] ?? '' }}</td>
                        <td>{{ $r['turma'] ?? '' }}</td>
                        <td>
                            @if($r['status'] === 'sucesso')
                                <span class="badge bg-success">Sucesso</span>
                            @elseif($r['status'] === 'erro')
                                <span class="badge bg-danger">Erro</span>
                            @elseif($r['status'] === 'aviso')
                                <span class="badge bg-warning text-dark">Aviso</span>
                            @else
                                <span class="badge bg-secondary">Ignorado</span>
                            @endif
                        </td>
                        <td>{{ $r['msg'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Nenhuma linha processada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
