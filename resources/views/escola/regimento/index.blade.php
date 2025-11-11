@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">📜 Regimento Escolar</h2>

    {{-- ✅ Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 🧩 Formulário de upload --}}
    <form action="{{ route('escola.regimento.update') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Enviar novo regimento (PDF)</label>
            <input type="file" name="arquivo" accept="application/pdf" class="form-control" required>
            <small class="text-muted">Apenas arquivos .pdf (máx. 4MB)</small>
        </div>
        <button class="btn btn-primary">💾 Salvar</button>
    </form>

    <hr>

    {{-- 📄 Visualização atual --}}
    @if($regimento && $regimento->arquivo)
        <h5 class="mb-3">📄 Regimento atual</h5>
        <iframe src="{{ asset('storage/'.$regimento->arquivo) }}" width="100%" height="700"
                style="border:1px solid #ccc; border-radius:8px;"></iframe>
    @else
        <p class="text-muted mt-3">Nenhum regimento cadastrado até o momento.</p>
    @endif
</div>
@endsection
