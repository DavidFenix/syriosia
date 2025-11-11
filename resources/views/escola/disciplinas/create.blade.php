@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nova Disciplina</h1>
    <form action="{{ route('escola.disciplinas.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label class="form-label">Abreviação</label>
            <input type="text" name="abr" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <input type="text" name="descr_d" class="form-control" required>
        </div>
        <button class="btn btn-success">Salvar</button>
        <a href="{{ route('escola.disciplinas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
