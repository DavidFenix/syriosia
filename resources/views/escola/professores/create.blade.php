@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Novo Professor</h1>
    <form action="{{ route('escola.professores.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="usuario_id" class="form-label">Usuário ID</label>
            <input type="number" name="usuario_id" class="form-control" required>
        </div>
        <button class="btn btn-success">Salvar</button>
        <a href="{{ route('escola.professores.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
