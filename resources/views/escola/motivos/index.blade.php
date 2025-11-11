@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">🧩 Motivos de Ocorrência</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('escola.motivos.create') }}" class="btn btn-primary mb-3">➕ Novo Motivo</a>

    <a href="{{ route('escola.motivos.importar') }}" class="btn btn-primary mb-3">📥 Importar Motivos</a>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-striped align-middle" id="tabela-motivos">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($motivos as $i => $m)
                    <tr>
                        <td>{{ $motivos->firstItem() + $i }}</td>
                        <td>{{ $m->descricao }}</td>
                        <td>{{ $m->categoria ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('escola.motivos.edit', $m) }}" class="btn btn-sm btn-outline-warning">✏️ Editar</a>
                            
                            @if(DB::table(prefix('ocorrencia_motivo'))->where('modelo_motivo_id', $m->id)->exists())
                                <span class="badge bg-secondary">Em uso</span>
                            @else
                                <form action="{{ route('escola.motivos.destroy', $m) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Excluir este motivo?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">🗑 Excluir</button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhum motivo cadastrado ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    initDataTable('#tabela-motivos', {
        order: [[2, 'asc'],[1, 'asc']],
        pageLength: 25
    }, [1, 2]);
});
</script>
@endpush