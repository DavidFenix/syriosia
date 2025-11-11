@extends('layouts.app')
@section('title','Gestão de Usuários')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Usuários</h1>
      <a href="{{ route('master.usuarios.create') }}" class="btn btn-primary">+ Novo Usuário</a>
    </div>

    @include('master.usuarios._list', ['usuarios' => $usuarios])
</div>
@endsection


{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuários</h1>
    @include('master.usuarios._list', ['usuarios' => $usuarios])
</div>
@endsection
--}}


{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuários</h1>

    <a href="{{ route('master.usuarios.create') }}" class="btn btn-primary mb-3">Novo Usuário</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Escola</th>
                <th>Roles</th>
                <th>Status</th>
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
                            <span class="badge bg-secondary">{{ $role->role_name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $usuario->status ? 'Ativo' : 'Inativo' }}</td>
                    <td>
                        <a href="{{ route('master.usuarios.edit', $usuario) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('master.usuarios.destroy', $usuario) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este usuário?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
--}}