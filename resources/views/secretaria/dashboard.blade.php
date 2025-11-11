@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Secretaria: {{ $secretaria->nome_e }}</h1>

    <h2>Escolas vinculadas</h2>
    <ul>
        @foreach($escolasFilhas as $e)
            <li>{{ $e->nome_e }}</li>
        @endforeach
    </ul>

    <h2>Usuários vinculados</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Escola</th>
                <th>Roles</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $u)
                <tr>
                    <td>{{ $u->nome_u }}</td>
                    <td>{{ $u->cpf }}</td>
                    <td>{{ $u->escola->nome_e }}</td>
                    <td>{{ $u->roles->pluck('role_name')->join(', ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
