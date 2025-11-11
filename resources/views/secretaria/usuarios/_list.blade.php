{{-- Lista de Usuários --}}
<!--a href="{{ route('master.usuarios.create') }}" class="btn btn-success mb-3">+ Novo Usuário</a-->
<form method="get" class="row g-2 mb-3">
  <div class="col-auto">
    <select name="tipo" class="form-select">
      <option value="">Todos</option>
      <option value="mae"   {{ ($filtro ?? '') === 'mae' ? 'selected' : '' }}>Somente Secretarias (mães)</option>
      <option value="filha" {{ ($filtro ?? '') === 'filha' ? 'selected' : '' }}>Somente Escolas (filhas)</option>
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary">Filtrar</button>
  </div>
</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>CPF</th>
            <th>Status</th>
            <th>Escola</th>
            <th>Roles</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->id }}</td>
                <td>{{ $usuario->nome_u }}</td>
                <td>{{ $usuario->cpf }}</td>
                <td>{{ $usuario->status ? 'Ativo' : 'Inativo' }}</td>
                <td>{{ $usuario->escola->nome_e ?? '-' }}</td>
                <td>
                    {{ $usuario->roles->pluck('role_name')->implode(', ') }}
                </td>
                <td>
                    <a href="{{ route('master.usuarios.edit', $usuario->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('master.usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Excluir este usuário?')">Excluir</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


{{-- Lista de Usuários -}}
<div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Escola</th>
                <th>Roles</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->nome_u }}</td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ $usuario->escola->nome_e ?? '-' }}</td>
                    <td>
                        @foreach($usuario->roles as $role)
                            <span class="badge bg-info">{{ $role->role_name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('master.usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('master.usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
--}}