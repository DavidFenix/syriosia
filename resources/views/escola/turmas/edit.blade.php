@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Turma</h1>
    <form action="{{ route('escola.turmas.update', $turma) }}" method="post">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Série/Turma</label>
            <input type="text" name="serie_turma" class="form-control" value="{{ $turma->serie_turma }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Turno</label>
            <input type="text" name="turno" class="form-control" value="{{ $turma->turno }}" required>
        </div>
        <button class="btn btn-success">Atualizar</button>
        <a href="{{ route('escola.turmas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
