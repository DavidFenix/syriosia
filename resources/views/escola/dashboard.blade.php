@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold">🏫 Painel da Escola</h2>
    <p class="text-muted mb-5">
        Bem-vindo, <strong>{{ Auth::user()->nome_u ?? 'Usuário' }}</strong>!
    </p>

    <div class="row g-3">

        {{-- 📅 Ocorrências de Hoje --}}
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm h-100 border-0 hover-card bg-light text-dark text-center p-3 position-relative">
                <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 rounded-bottom-end small">
                    HOJE
                </div>
                <div class="fs-1 mb-2">📅</div>
                <div class="fw-semibold">Ocorrências de Hoje</div>
                <div class="fs-1 mt-1 fw-bold text-danger">
                    {{ $totalOcorrenciasHoje }}
                </div>
                <div class="text-muted small mt-1">
                    ({{ now()->format('d/m/Y') }})
                </div>
            </div>
        </div>

        {{-- ⚠️ Ocorrências --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('professor.ocorrencias.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-danger text-white text-center p-3">
                    <div class="fs-1 mb-2">⚠️</div>
                    <div class="fw-semibold">Ocorrências</div>
                    <div class="fs-6 mt-1">
                        <span class="badge bg-light text-dark">Ativas: {{ $totalOcorrenciasAtivas }}</span><br>
                        <span class="badge bg-secondary">Arquivadas: {{ $totalOcorrenciasArquivadas }}</span><br>
                        <span class="badge bg-dark">Anuladas: {{ $totalOcorrenciasAnuladas }}</span>
                    </div>
                    <div class="fs-1 mt-2 fw-bold">Total: {{ $totalOcorrencias }}</div>
                </div>
            </a>
        </div>

        {{-- 👨‍🏫 Professores --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.professores.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-primary text-white text-center p-3">
                    <div class="fs-1 mb-2">👨‍🏫</div>
                    <div class="fw-semibold">Professores</div>
                    <div class="fs-1 mt-1 fw-bold">{{ $totalProfessores }}</div>
                </div>
            </a>
        </div>

        {{-- 🎓 Alunos --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.alunos.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-success text-white text-center p-3">
                    <div class="fs-1 mb-2">🎓</div>
                    <div class="fw-semibold">Alunos</div>
                    <div class="fs-1 mt-1 fw-bold">{{ $totalAlunos }}</div>
                </div>
            </a>
        </div>

        {{-- 🏷️ Turmas --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.turmas.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-info text-dark text-center p-3">
                    <div class="fs-1 mb-2">🏷️</div>
                    <div class="fw-semibold">Turmas</div>
                    <div class="fs-1 mt-1 fw-bold">{{ $totalTurmas }}</div>
                </div>
            </a>
        </div>

        {{-- 📚 Disciplinas --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.disciplinas.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-warning text-dark text-center p-3">
                    <div class="fs-1 mb-2">📚</div>
                    <div class="fw-semibold">Disciplinas</div>
                    <div class="fs-1 mt-1 fw-bold">{{ $totalDisciplinas }}</div>
                </div>
            </a>
        </div>

        {{-- 🧮 Enturmações --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.enturmacao.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-secondary text-white text-center p-3">
                    <div class="fs-1 mb-2">🧮</div>
                    <div class="fw-semibold">Enturmações</div>
                    <div class="fs-1 mt-1 fw-bold">{{ $totalEnturmacoes }}</div>
                </div>
            </a>
        </div>

        {{-- 🧩 Motivos --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.motivos.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-danger text-white text-center p-3">
                    <div class="fs-1 mb-2">🧩</div>
                    <div class="fw-semibold">Motivos</div>
                    <div class="fs-1 mt-1 fw-bold">{{ $totalMotivos }}</div>
                </div>
            </a>
        </div>

        {{-- 📜 Regimento Escolar --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.regimento.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-warning text-dark text-center p-3">
                    <div class="fs-1 mb-2">📜</div>
                    <div class="fw-semibold">Regimento Escolar</div>
                    <div class="fs-5 mt-1 fw-bold">
                        {{ $temRegimento ? '✅' : '—' }}
                    </div>
                </div>
            </a>
        </div>

        {{-- 🏫 Identidade da Escola --}}
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('escola.identidade.edit') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card bg-primary text-white text-center p-3">
                    <div class="fs-1 mb-2">🏫</div>
                    <div class="fw-semibold">Identidade</div>
                    <div class="fs-5 mt-1 fw-bold">{{ $escola->nome_e ?? '—' }}</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/*.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}
*/
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}
.card .position-absolute {
    font-weight: 600;
    letter-spacing: 0.5px;
}

</style>
@endpush



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard da Escola</h1>
    <p>Bem-vindo, {{ Auth::user()->nome_u ?? 'Usuário' }}!</p>

    <div class="row">
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.professores.index') }}" class="btn btn-primary w-100">
                👨‍🏫 Professores
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.alunos.index') }}" class="btn btn-success w-100">
                👩‍🎓 Alunos
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.disciplinas.index') }}" class="btn btn-warning w-100">
                📚 Disciplinas
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.turmas.index') }}" class="btn btn-info w-100">
                🏫 Turmas
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.enturmacao.index') }}" class="btn btn-warning w-100">
                📚 Enturmação
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.lotacao.index') }}" class="btn btn-warning w-100">
                📚 Lotação
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.motivos.index') }}" class="btn btn-warning w-100">
                📚 Motivos de Ocorrência
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.alunos.fotos.lote') }}" class="btn btn-warning w-100">
                📚 Upload em Massa de Fotos
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.regimento.index') }}" class="btn btn-warning w-100">
                📚 Regimento Escolar
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('escola.identidade.edit') }}" class="btn btn-warning w-100">
                📚 Identidade da Escola
            </a>
        </div>
    </div>
</div>
@endsection
--}}