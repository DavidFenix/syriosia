@extends('layouts.app')
@section('title','Gestão de Funções')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Funções Definidas</h1>
        <!--a href="{{-- route('master.roles.create') --}}" class="btn btn-primary">+ Nova Função</a-->
    </div>
    @include('master.roles._list', ['roles' => $roles])
</div>
@endsection













{{--
@extends('layouts.app')

@section('content')
<h1>Roles</h1>

<a href="{{ route('master.roles.create') }}" class="btn btn-primary">Nova Role</a>

<table class="table mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($roles as $role)
        <tr>
            <td>{{ $role->id }}</td>
            <td>{{ $role->role_name }}</td>
            <td>
                <a href="{{ route('master.roles.edit', $role) }}" class="btn btn-sm btn-warning">Editar</a>
                <form action="{{ route('master.roles.destroy', $role) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
--}}