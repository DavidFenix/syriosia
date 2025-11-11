@extends('layouts.app')

@section('content')
<div class="container">
    <h2>📥 Importar Motivos de Outras Escolas</h2>
    <p class="text-muted">
        Selecione os motivos que deseja importar para a sua escola.
        Motivos duplicados (já existentes) serão ignorados automaticamente.
    </p>

    <form method="POST" action="{{ route('escola.motivos.importar.salvar') }}">
        @csrf
        <table class="table table-striped table-bordered" id="tabela-importar">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Escola de Origem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($motivosOutros as $m)
                    <tr>
                        <td><input type="checkbox" name="motivos[]" value="{{ $m->id }}"></td>
                        <td>{{ $m->descricao }}</td>
                        <td>{{ $m->categoria ?? '—' }}</td>
                        <td class="text-muted">{{ $m->escola->nome_e ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Nenhum motivo disponível para importação.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">
                ✅ Importar Motivos Selecionados
            </button>
            <a href="{{ route('escola.motivos.index') }}" class="btn btn-secondary">↩️ Voltar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    initDataTable('#tabela-importar', {
        order: [[2, 'asc']],
        pageLength: 25
    }, [1, 2, 3]);

    $('#checkAll').on('change', function() {
        $('input[name="motivos[]"]').prop('checked', this.checked);
    });

});
</script>
@endpush

