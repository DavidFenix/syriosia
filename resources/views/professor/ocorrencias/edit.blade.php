@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">✏️ Editar Ocorrência</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('professor.ocorrencias.update', $ocorrencia->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Informações básicas</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $ocorrencia->descricao) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Local</label>
                        <input type="text" name="local" class="form-control"
                               value="{{ old('local', $ocorrencia->local) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Atitude</label>
                        <input type="text" name="atitude" class="form-control"
                               value="{{ old('atitude', $ocorrencia->atitude) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Outra atitude</label>
                        <input type="text" name="outra_atitude" class="form-control"
                               value="{{ old('outra_atitude', $ocorrencia->outra_atitude) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Comportamento</label>
                        <input type="text" name="comportamento" class="form-control"
                               value="{{ old('comportamento', $ocorrencia->comportamento) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Sugestão</label>
                    <textarea name="sugestao" class="form-control" rows="2">{{ old('sugestao', $ocorrencia->sugestao) }}</textarea>
                </div>

                {{-- Motivos --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Motivos</label>
                    <div class="row">
                        @foreach($motivos as $m)
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input type="checkbox" name="motivos[]" value="{{ $m->id }}"
                                        id="motivo{{ $m->id }}"
                                        class="form-check-input"
                                        {{ $ocorrencia->motivos->contains($m->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="motivo{{ $m->id }}">{{ $m->descricao }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('professor.ocorrencias.show', $ocorrencia->id) }}" class="btn btn-outline-secondary">🔙 Voltar</a>
            <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection
