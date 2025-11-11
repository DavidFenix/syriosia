{{--
@extends('layouts.app')

@section('content')
<h1>Editar Role</h1>

<form method="POST" action="{{ route('master.roles.update', $role) }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Nome da Role</label>
        <input type="text" name="role_name" class="form-control" value="{{ $role->role_name }}" required>
    </div>
    <button type="submit" class="btn btn-success">Atualizar</button>
</form>
@endsection
--}}