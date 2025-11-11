@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>✏️ Editar Motivo</h2>

    <form action="{{ route('escola.motivos.update', $motivo) }}" method="POST" class="mt-3">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" required rows="3">{{ old('descricao', $motivo->descricao) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria (opcional)</label>
            <input type="text" name="categoria" class="form-control" value="{{ old('categoria', $motivo->categoria) }}">
        </div>
        <button class="btn btn-primary">💾 Atualizar</button>
        <a href="{{ route('escola.motivos.index') }}" class="btn btn-secondary">↩ Voltar</a>
    </form>
</div>
@endsection
