@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h1 class="mb-4">👨‍🏫 Painel do Professor</h1>

    <div class="row g-4">

        {{-- 📚 Ofertas --}}
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="display-5 text-primary">{{ $totalOfertas }}</div>
                    <p class="fw-semibold mb-1">Ofertas</p>
                    <small class="text-muted">Disciplinas e turmas - {{ $ano }}</small>
                </div>
            </div>
        </div>

        {{-- ⚠️ Ocorrências aplicadas --}}
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="display-5 text-warning">{{ $totalOcorrencias }}</div>
                    <p class="fw-semibold mb-1">Ocorrências Aplicadas</p>
                    <small class="text-muted">Em todas as turmas - {{ $ano }}</small>
                </div>
            </div>
        </div>

        {{-- 🟢 Ativas --}}
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="display-5 text-success">{{ $ocorrenciasAtivas }}</div>
                    <p class="fw-semibold mb-1">Ocorrências Ativas</p>
                    <small class="text-muted">Em andamento - {{ $ano }}</small>
                </div>
            </div>
        </div>

        {{-- ⚪ Arquivadas --}}
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="display-5 text-secondary">{{ $ocorrenciasArquivadas }}</div>
                    <p class="fw-semibold mb-1">Ocorrências Arquivadas</p>
                    <small class="text-muted">Finalizadas - {{ $ano }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- 🔗 Acesso rápido --}}
    <div class="mt-5 text-center">
        <a href="{{ route('professor.ofertas.index') }}" class="btn btn-primary me-2">
            📘 Ver Minhas Ofertas
        </a>
        <a href="{{ route('professor.ocorrencias.index') }}" class="btn btn-warning">
            ⚠️ Gerenciar Ocorrências
        </a>
    </div>

</div>

<style>
.display-5 {
    font-size: 2.8rem;
    font-weight: bold;
}
.card:hover {
    transform: translateY(-3px);
    transition: 0.2s;
}
</style>
@endsection

{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">📘 Dashboard do Professor</h1>

    <div class="alert alert-info">
        Bem-vindo, {{ Auth::user()->nome_u ?? 'Professor' }}!
    </div>

    <p>
        Aqui será o painel inicial dos professores.
        Você poderá futuramente visualizar suas turmas, disciplinas, registros e notificações.
    </p>

    <ul>
        <li><strong>Turmas</strong> que você leciona</li>
        <li><strong>Disciplinas</strong> associadas</li>
        <li><strong>Ocorrências</strong> e registros</li>
    </ul>
</div>
@endsection
--}}