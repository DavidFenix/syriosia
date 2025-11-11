@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Painel Master</h1>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <hr>
    <h2>🏫 Nova Escola</h2>
    <form method="POST" action="{{ route('master.storeEscola') }}">
        @csrf
        <input type="text" name="nome_e" placeholder="Nome da Instituição" required>
        <button type="submit">Salvar</button>
    </form>

    <hr>
    <h2>➕ Nova Role</h2>
    <form method="POST" action="{{ route('master.storeRole') }}">
        @csrf
        <input type="text" name="role_name" placeholder="Nome da role" required>
        <button type="submit">Salvar</button>
    </form>

    <hr>
    <h2>👤 Novo Usuário</h2>
    <form method="POST" action="{{ route('master.storeUsuario') }}">
        @csrf
        <input type="text" name="nome_u" placeholder="Nome" required>
        <input type="text" name="cpf" placeholder="CPF" required>
        <input type="password" name="senha" placeholder="Senha" required>

        <select name="school_id" required>
            <option value="">Selecione Escola</option>
            @foreach($escolas as $e)
                <option value="{{ $e->id }}">{{ $e->nome_e }}</option>
            @endforeach
        </select>

        <label>Roles:</label>
        @foreach($roles as $r)
            <input type="checkbox" name="roles[]" value="{{ $r->id }}"> {{ $r->role_name }}
        @endforeach

        <button type="submit">Salvar</button>
    </form>

    <hr>
    <h2>📋 Usuários</h2>
    <table class="table table-bordered">
        <tr>
            <th>ID</th><th>Nome</th><th>CPF</th><th>Escola</th><th>Roles</th><th>Ações</th>
        </tr>
        @foreach($usuarios as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->nome_u }}</td>
            <td>{{ $u->cpf }}</td>
            <td>{{ $u->escola->nome_e ?? '-' }}</td>
            <td>{{ $u->roles->pluck('role_name')->join(', ') }}</td>
            <td>
                <form method="POST" action="{{ route('master.destroyUsuario',$u->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit">Excluir</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
