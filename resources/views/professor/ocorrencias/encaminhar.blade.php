@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">📁 Encaminhar / Arquivar Ocorrência</h2>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Dados principais --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Ocorrência #{{ $ocorrencia->id }}</h5>
            <p><strong>Aluno:</strong> {{ $ocorrencia->aluno->nome_a }}</p>
            <p><strong>Professor:</strong> {{ $ocorrencia->professor->usuario->nome_u ?? '-' }}</p>
            <p><strong>Descrição:</strong><br>{{ $ocorrencia->descricao ?? '—' }}</p>
        </div>
    </div>

    {{-- Formulário --}}
    <form action="{{ route('professor.ocorrencias.encaminhar.salvar', $ocorrencia->id) }}" method="POST">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Registrar encaminhamento</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Situação</label>
                    <select name="status" class="form-select" required>
                        <option value="1" {{ $ocorrencia->status == 1 ? 'selected' : '' }}>Ativa</option>
                        <option value="0" {{ $ocorrencia->status == 0 ? 'selected' : '' }}>Arquivar</option>
                        <option value="2" {{ $ocorrencia->status == 2 ? 'selected' : '' }}>Anular</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Encaminhamento / Observações</label>
                    <textarea name="encaminhamentos" rows="4" class="form-control">{{ old('encaminhamentos', $ocorrencia->encaminhamentos) }}</textarea>
                    <small class="text-muted">Descreva o que foi decidido, providências ou observações.</small>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('professor.ocorrencias.show', $ocorrencia->id) }}" class="btn btn-outline-secondary">
                🔙 Voltar
            </a>
            <button type="submit" class="btn btn-success">
                💾 Salvar Encaminhamento
            </button>
        </div>
    </form>
</div>
@endsection
