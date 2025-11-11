@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Disciplina</h1>
    <form action="{{ route('escola.disciplinas.update', $disciplina) }}" method="post">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Abreviação</label>
            <input type="text" name="abr" class="form-control" value="{{ $disciplina->abr }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <input type="text" name="descr_d" class="form-control" value="{{ $disciplina->descr_d }}" required>
        </div>
        <button class="btn btn-success">Atualizar</button>
        <a href="{{ route('escola.disciplinas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
