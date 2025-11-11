@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">📦 Upload em Massa de Fotos de Alunos</h2>

    <div class="alert alert-info">
        <strong>Instruções:</strong>
        <ul class="mb-0">
            <li>Os arquivos devem estar no formato <code>.png</code> ou <code>.jpg</code>.</li>
            <li>O nome do arquivo deve conter <strong>somente a matrícula do aluno</strong>. Exemplo: <code>12345.png</code>.</li>
            <li>O sistema adicionará automaticamente o prefixo do ID da escola.</li>
            <li>Fotos sem correspondência serão ignoradas.</li>
        </ul>
    </div>

    <form action="{{ route('escola.alunos.fotos.lote.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Selecionar várias fotos</label>
            <input type="file" name="fotos[]" accept="image/*" multiple required class="form-control">
        </div>

        <button type="submit" class="btn btn-success">🚀 Enviar Fotos</button>
        <a href="{{ route('escola.alunos.index') }}" class="btn btn-secondary">↩ Voltar</a>
    </form>
</div>
@endsection
