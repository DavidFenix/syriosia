@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">📊 Resultado do Envio de Fotos</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-success mb-3">
                <div class="card-header bg-success text-white">✅ Sucesso ({{ count($resultados['sucesso']) }})</div>
                <div class="card-body small">
                    @forelse($resultados['sucesso'] as $ok)
                        <div>📸 {{ $ok }}</div>
                    @empty
                        <em>Nenhum arquivo.</em>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning text-dark">⚠️ Ignorados ({{ count($resultados['ignorado']) }})</div>
                <div class="card-body small">
                    @forelse($resultados['ignorado'] as $skip)
                        <div>⚪ {{ $skip }}</div>
                    @empty
                        <em>Nenhum arquivo.</em>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white">❌ Erros ({{ count($resultados['erro']) }})</div>
                <div class="card-body small">
                    @forelse($resultados['erro'] as $err)
                        <div>🚫 {{ $err }}</div>
                    @empty
                        <em>Nenhum erro.</em>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('escola.alunos.fotos.lote') }}" class="btn btn-primary mt-3">⬅️ Novo Envio</a>
    <a href="{{ route('escola.alunos.index') }}" class="btn btn-secondary mt-3">🏫 Voltar à lista de alunos</a>
</div>
@endsection
