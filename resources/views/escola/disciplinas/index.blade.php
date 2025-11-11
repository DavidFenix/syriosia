@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Disciplinas</h1>
    <a href="{{ route('escola.disciplinas.create') }}" class="btn btn-primary mb-3">➕ Nova Disciplina</a>

    <table class="table table-striped" id="tabela-disciplinas">
        <thead>
            <tr>
                <th>#</th>
                <th>Abreviação</th>
                <th>Descrição</th>
                <th>Escola Origem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @php $i = 1; @endphp
        @forelse($disciplinas as $d)
          <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $d->abr }}</td>
            <td>{{ $d->descr_d }}</td>
            <td>
                {{ optional($d->escola)->nome_e ?? '—' }}
            </td>
            <td>
              <a href="{{ route('escola.disciplinas.edit',$d) }}" class="btn btn-sm btn-warning">✏️</a>
              <form action="{{ route('escola.disciplinas.destroy',$d) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Remover esta disciplina?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">🗑</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Nenhuma disciplina</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    initDataTable('#tabela-disciplinas', {
        order: [[2, 'asc']],
        columnDefs: [
            { width: '5%', targets: 0 },
            { width: '10%', targets: 1 },
            { width: '40%', targets: 2 },
            { width: '30%', targets: 3 },
            { width: '15%', targets: 4, orderable: false, className: 'text-end' }
        ]
    }, [1, 2, 3]);
});
</script>
@endpush





{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Disciplinas</h1>
    <a href="{{ route('escola.disciplinas.create') }}" class="btn btn-primary mb-3">➕ Nova Disciplina</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Abreviação</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($disciplinas as $disc)
                <tr>
                    <td>{{ $disc->id }}</td>
                    <td>{{ $disc->abr }}</td>
                    <td>{{ $disc->descr_d }}</td>
                    <td>
                        <a href="{{ route('escola.disciplinas.edit', $disc) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('escola.disciplinas.destroy', $disc) }}" method="post" style="display:inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir disciplina?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Nenhuma disciplina cadastrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
--}}

{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Disciplinas</h1>
    <a href="{{ route('escola.disciplinas.create') }}" class="btn btn-primary mb-3">Nova Disciplina</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Abr.</th>
                <th>Descrição</th>
                <th>Escola</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($disciplinas as $disc)
            <tr>
                <td>{{ $disc->id }}</td>
                <td>{{ $disc->abr }}</td>
                <td>{{ $disc->descr_d }}</td>
                <td>{{ $disc->escola->nome_e ?? '-' }}</td>
                <td>
                    <a href="{{ route('escola.disciplinas.edit', $disc) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('escola.disciplinas.destroy', $disc) }}" method="post" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir disciplina?')">Excluir</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Nenhuma disciplina encontrada.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
--}}