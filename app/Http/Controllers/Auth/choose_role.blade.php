@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Escolha seu Papel em {{ $escola->nome_e }}</h1>
    <ul class="list-group">
        @foreach($roles as $role)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ ucfirst($role->role_name) }}
                <form action="{{ route('set.context') }}" method="POST">
                    @csrf
                    <input type="hidden" name="school_id" value="{{ $schoolId }}">
                    <input type="hidden" name="role_name" value="{{ $role->role_name }}">
                    <button type="submit" class="btn btn-sm btn-success">Entrar</button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
@endsection
