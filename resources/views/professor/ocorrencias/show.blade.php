@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">🔍 Detalhes da Ocorrência</h2>

    {{-- ✅ Mensagens de retorno --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 🧱 Card principal --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Informações principais</h5>

            <p><strong>Aluno:</strong> {{ $ocorrencia->aluno->nome_a }}</p>
            <p><strong>Matrícula:</strong> {{ $ocorrencia->aluno->matricula }}</p>
            <p><strong>Professor:</strong> {{ $ocorrencia->professor->usuario->nome_u ?? '-' }}</p>
            <p><strong>Turma:</strong> {{ $ocorrencia->oferta->turma->serie_turma ?? '—' }}</p>
            <p><strong>Disciplina:</strong> {{ $ocorrencia->oferta->disciplina->descr_d ?? '—' }}</p>

            <p><strong>Descrição:</strong><br>{{ $ocorrencia->descricao ?? '—' }}</p>

            {{-- Motivos --}}
            <div class="mt-3">
                <strong>Motivos:</strong>
                @if($ocorrencia->motivos->count() > 0)
                    <ul class="mb-0 small">
                        @foreach($ocorrencia->motivos as $m)
                            <li>{{ $m->descricao }}</li>
                        @endforeach
                    </ul>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>

            {{-- Encaminhamento --}}
            @if(!empty($ocorrencia->encaminhamentos))
                <div class="mt-3 p-3 bg-light rounded border">
                    <strong>Encaminhamentos / Observações:</strong><br>
                    {{ $ocorrencia->encaminhamentos }}
                    <br>
                    <small class="text-muted">
                        Recebido em: {{ $ocorrencia->recebido_em ? $ocorrencia->recebido_em->format('d/m/Y H:i') : '—' }}
                    </small>
                </div>
            @endif
        </div>
    </div>

    {{-- ⚙️ Ações --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <a href="{{ route('professor.ocorrencias.index') }}" class="btn btn-outline-secondary">
                ⬅ Voltar
            </a>
        </div>

        <div class="btn-group" role="group">
            {{-- ✏️ Editar --}}
            @if($permissoes['autor'])
                <a href="{{ route('professor.ocorrencias.edit', $ocorrencia->id) }}"
                   class="btn btn-outline-warning">✏️ Editar</a>
            @endif

            {{-- 🗑 Excluir --}}
            @if($permissoes['autor'])
                <form action="{{ route('professor.ocorrencias.destroy', $ocorrencia->id) }}"
                      method="POST" onsubmit="return confirm('Deseja realmente excluir esta ocorrência?')"
                      class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">🗑 Excluir</button>
                </form>
            @endif

            {{-- 📁 Encaminhar / Arquivar --}}
            @if($permissoes['diretor'])
                <a href="{{ route('professor.ocorrencias.encaminhar', $ocorrencia->id) }}"
                   class="btn btn-outline-success">
                    📁 Encaminhar / Arquivar
                </a>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .card-body p {
        margin-bottom: 6px;
    }
    ul.small li {
        line-height: 1.4;
    }
</style>
@endpush





{{--
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-4">📘 Detalhes da Ocorrência</h2>

    {{-- ✅ Mensagens de retorno -}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 👤 Aluno e turma -}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex align-items-center">
            @php
                $fotoNome = $ocorrencia->aluno->matricula . '.png';
                $fotoPath = public_path('storage/img-user/' . $fotoNome);
                $fotoUrl = file_exists($fotoPath)
                    ? asset('storage/img-user/' . $fotoNome)
                    : asset('storage/img-user/padrao.png');

                $turma = $ocorrencia->oferta->turma->serie_turma ?? '-';
                $disciplina = $ocorrencia->oferta->disciplina->abr ?? '-';
            @endphp

            <img src="{{ $fotoUrl }}" alt="Foto do aluno"
                 class="rounded-circle me-3 shadow-sm"
                 style="width:80px; height:80px; object-fit:cover;">

            <div>
                <h5 class="mb-1">{{ $ocorrencia->aluno->nome_a }}</h5>
                <div class="text-muted small">
                    Matrícula: {{ $ocorrencia->aluno->matricula }}<br>
                    Turma: {{ $turma }}<br>
                    Disciplina: {{ $disciplina }}
                </div>
            </div>
        </div>
    </div>

    {{-- 🧾 Informações da ocorrência -}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">🗓 Ocorrência registrada em {{ $ocorrencia->created_at->format('d/m/Y H:i') }}</h5>
            <p><strong>Professor:</strong> {{ $ocorrencia->professor->usuario->nome_u ?? '-' }}</p>

            <p><strong>Descrição:</strong><br>
                {{ $ocorrencia->descricao ?? '—' }}
            </p>

            <p><strong>Local:</strong> {{ $ocorrencia->local ?? '—' }}</p>
            <p><strong>Atitude:</strong> {{ $ocorrencia->atitude ?? '—' }}</p>
            @if($ocorrencia->outra_atitude)
                <p><strong>Outra atitude:</strong> {{ $ocorrencia->outra_atitude }}</p>
            @endif
            @if($ocorrencia->comportamento)
                <p><strong>Comportamento:</strong> {{ $ocorrencia->comportamento }}</p>
            @endif
            @if($ocorrencia->sugestao)
                <p><strong>Sugestão:</strong> {{ $ocorrencia->sugestao }}</p>
            @endif

            {{-- Motivos -}}
            <p class="mt-3"><strong>Motivos:</strong></p>
            @if($ocorrencia->motivos->count() > 0)
                <ul>
                    @foreach($ocorrencia->motivos as $m)
                        <li>{{ $m->descricao }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Nenhum motivo registrado.</p>
            @endif

            {{-- Status -}}
            <p class="mt-3">
                <strong>Status:</strong>
                @if($ocorrencia->status == 1)
                    <span class="badge bg-success">Ativa</span>
                @elseif($ocorrencia->status == 0)
                    <span class="badge bg-secondary">Arquivada</span>
                @elseif($ocorrencia->status == 2)
                    <span class="badge bg-danger">Anulada</span>
                @endif
            </p>
        </div>
    </div>

    {{-- ⚙️ Ações dinâmicas -}}
    <div class="d-flex justify-content-between align-items-center mt-4">

        <a href="{{ route('professor.ocorrencias.index') }}" class="btn btn-outline-secondary">
            🔙 Voltar
        </a>

        <div class="d-flex gap-2">
            {{-- ✏️ Editar -}}
            @if($permissoes['autor'])
                <a href="{{ route('professor.ocorrencias.edit', $ocorrencia->id) }}" class="btn btn-outline-warning">
                    ✏️ Editar
                </a>
            @endif

            {{-- 🗑 Excluir -}}
            @if($permissoes['autor'])
                <form action="{{ route('professor.ocorrencias.destroy', $ocorrencia->id) }}" method="POST"
                      onsubmit="return confirm('Deseja realmente excluir esta ocorrência?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        🗑 Excluir
                    </button>
                </form>
            @endif

            {{-- 📁 Encaminhar / Arquivar -}}
            @if($permissoes['diretor'])
                <a href="{{ route('professor.ocorrencias.encaminhar', $ocorrencia->id) }}" class="btn btn-outline-success">
                    📁 Encaminhar / Arquivar
                </a>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .card {
        border-radius: 12px;
    }
</style>
@endpush
--}}