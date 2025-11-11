{{--
@extends('layouts.app')

@section('content')
<h1>Nova Role</h1>

<form method="POST" action="{{ route('master.roles.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Nome da Role</label>
        <input type="text" name="role_name" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Salvar</button>
</form>
@endsection
--}}