@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Escolha o Papel</h1>
    <p>Você possui múltiplos papéis na escola <strong>{{ $escola->nome_e }}</strong>. Selecione um:</p>

    <form action="{{ route('set.context') }}" method="POST">
        @csrf
        <input type="hidden" name="school_id" value="{{ $schoolId }}">

        @foreach($roles as $role)
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="role_name" value="{{ $role->role_name }}" id="role-{{ $role->id }}" required>
                <label class="form-check-label" for="role-{{ $role->id }}">
                    {{ ucfirst($role->role_name) }}
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-success mt-3">Entrar</button>
        <a href="{{ route('choose.school') }}" class="btn btn-secondary mt-3">Voltar</a>
    </form>
</div>
@endsection
