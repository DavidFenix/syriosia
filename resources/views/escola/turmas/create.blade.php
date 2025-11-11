@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nova Turma</h1>
    <form action="{{ route('escola.turmas.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label class="form-label">Série/Turma</label>
            <input type="text" name="serie_turma" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Turno</label>
            <input type="text" name="turno" class="form-control" required>
        </div>
        <button class="btn btn-success">Salvar</button>
        <a href="{{ route('escola.turmas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
