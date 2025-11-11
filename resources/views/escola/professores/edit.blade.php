@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Professor</h1>
    <form action="{{ route('escola.professores.update', $professor) }}" method="post">
        @csrf @method('PUT')
        <div class="mb-3">
            <label for="usuario_id" class="form-label">Usuário ID</label>
            <input type="number" name="usuario_id" class="form-control" value="{{ $professor->usuario_id }}" required>
        </div>
        <button class="btn btn-success">Atualizar</button>
        <a href="{{ route('escola.professores.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
