@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Enturmações — {{ $anoLetivo }}</h1>
    <a href="{{ route('escola.enturmacao.create') }}" class="btn btn-primary mb-3">➕ Nova Enturmação</a>

    <table class="table table-striped" id="tabela-enturmacao">
        <thead>
            <tr>
                <th>#</th>
                <th>Aluno</th>
                <th>Turma</th>
                <th>Ano Letivo</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($enturmacoes as $e)
            <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->aluno->nome_a ?? '—' }}</td>
                <td>{{ $e->turma->serie_turma ?? '—' }}</td>
                <td>{{ $e->ano_letivo }}</td>
                <td>{{ $e->vigente ? '✅ Ativa' : '⏸️ Encerrada' }}</td>
                <td class="text-end">
                    <form action="{{ route('escola.enturmacao.destroy', $e) }}" method="POST"
                          onsubmit="return confirm('Remover vínculo deste aluno com a turma?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">🗑</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Nenhuma enturmação encontrada</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Aplica o DataTable com filtro nas colunas Nome(1), CPF(2), Status(3), Roles(4)
    initDataTable('#tabela-enturmacao', {
        order: [[2, 'asc'],[1, 'asc']],
        pageLength: 10
    }, [1, 2, 3, 4]);
});
</script>
@endpush
