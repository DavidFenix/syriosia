@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>➕ Novo Motivo de Ocorrência</h2>

    <form action="{{ route('escola.motivos.store') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" required rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria (opcional)</label>
            <input type="text" name="categoria" class="form-control">
        </div>
        <button class="btn btn-success">💾 Salvar</button>
        <a href="{{ route('escola.motivos.index') }}" class="btn btn-secondary">↩ Voltar</a>
    </form>
</div>
@endsection
