@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Professores da Escola e demais Vinculados</h1>
    <a href="{{ route('escola.usuarios.index') }}" class="btn btn-primary mb-3">➕ Vincular ou Criar Usuário</a>

    @if($mensagem)
      <div class="alert alert-success">
        {{ $mensagem }}
      </div>
    @endif

    <table class="table table-striped" id="tabela-professores-escola">
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuário</th>
          <th>Escola de origem</th>
          <th>Vínculo</th>
          <th>Status</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>

        @forelse($professores as $p)
          
           @php
                $schoolAtual = session('current_school_id');
                $schoolProfessor = $p->school_id;
                $schoolUsuario = $p->usuario->school_id ?? null;
                $isNativo = $schoolUsuario == $schoolAtual;
                $status = $p->usuario->status ?? 0;
            @endphp

          <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->usuario->nome_u ?? '-' }}</td>
            <td>{{ $p->usuario->escola->nome_e ?? '-' }}</td>
            <td>
              @if($isNativo)
                <span class="badge bg-success">Nativo</span>
              @else
                <span class="badge bg-warning text-dark">Vinculado</span>
              @endif
            </td>
            <td>
              @if($status)
                <span class="badge bg-primary">Ativo</span>
              @else
                <span class="badge bg-secondary">Inativo</span>
              @endif
            </td>
            <td class="text-end">
              @if($p->id !== auth()->id())
                <form action="{{ route('escola.professores.destroy', $p) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Remover este professor?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">🗑</button>
                </form>
              @else
                <button class="btn btn-sm btn-secondary" disabled title="Você não pode excluir a si mesmo">🔒</button>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nenhum professor</td></tr>
        @endforelse
      </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Aplica o DataTable com filtro nas colunas Nome(1), CPF(2), Status(3), Roles(4)
    initDataTable('#tabela-professores-escola', {
        order: [[4, 'asc'],[3, 'asc'],[2, 'asc'],[1, 'asc']],
        pageLength: 10
    }, [1, 2, 3, 4]);
});
</script>
@endpush


{{--
@extends('layouts.app')

    @section('content')
    <div class="container">
        <h1>Professores da Escola e demais Vinculados</h1>
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-primary mb-3">➕ Vincular ou Criar Usuário</a>

        @if($mensagem)
          <div class="alert alert-success">
            {{ $mensagem }}
          </div>
        @endif

        <table class="table table-striped" id="tabela-professores-escola">
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuário</th>
              <th>Escola de origem</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($professores as $p)
              <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->usuario->nome_u ?? '-' }}</td>
                <td>{{ $p->usuario->escola->nome_e ?? '-' }}</td>
                <td>

                  @if($p->id !== auth()->id())
                  <form action="{{ route('escola.professores.destroy', $p) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Remover este professor?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">🗑</button>
                  </form>
                  @else
                    <button class="btn btn-sm btn-secondary" disabled title="Você não pode excluir a si mesmo">🔒</button>
                  @endif

                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">Nenhum professor</td></tr>
            @endforelse
          </tbody>
        </table>
    </div>
    @endsection
--}}

{{--
@section('content')
    <div class="container">
        <h1>Professores da Escola e demais Vinculados</h1>
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-primary mb-3">➕ Vincular ou Criar Usuário</a>

        <table class="table table-striped">
            <thead><tr><th>ID</th><th>Usuário</th><th>Escola de Origem</th><th>Ações</th></tr></thead>
            <tbody>
            @forelse($professores as $p)
              <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->usuario->nome_u ?? '-' }}</td>
                <td>{{ $p->usuario->escola->nome_e ?? '—' }}</td>
                <td>
                  <form action="{{ route('escola.professores.destroy',$p) }}" method="POST" class="d-inline" 
                        onsubmit="return confirm('Remover este professor?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">🗑</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">Nenhum professor</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @endsection
    --}}


    {{--
    <div class="container">
        <h1>Professores</h1>
        <a href="{{ route('escola.professores.create') }}" class="btn btn-primary mb-3">➕ Novo Professor</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($professores as $prof)
                    <tr>
                        <td>{{ $prof->id }}</td>
                        <td>{{ $prof->usuario_id }}</td>
                        <td>
                            <a href="{{ route('escola.professores.edit', $prof) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('escola.professores.destroy', $prof) }}" method="post" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir professor?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">Nenhum professor encontrado.</td></tr>
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
        <h1>Professores</h1>
        <a href="{{ route('escola.professores.create') }}" class="btn btn-primary mb-3">Novo Professor</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>Escola</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            @forelse($professores as $prof)
                <tr>
                    <td>{{ $prof->id }}</td>
                    <td>{{ $prof->usuario->nome_u ?? '-' }}</td>
                    <td>{{ $prof->escola->nome_e ?? '-' }}</td>
                    <td>
                        <a href="{{ route('escola.professores.edit', $prof) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('escola.professores.destroy', $prof) }}" method="post" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir professor?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Nenhum professor encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @endsection
--}}