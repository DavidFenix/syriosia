@extends('layouts.app')

@section('content')
<div class="container py-3">

    @include('components.pdf_header')
    
    {{-- 🔙 Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📜 Histórico de Ocorrências</h2>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">⬅ Voltar</a>
    </div>

    {{-- ===================== INFORMAÇÕES DO ALUNO ===================== --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex align-items-center">
            @php
                $fotoNome = $aluno->matricula . '.png';
                $fotoPath = public_path('storage/img-user/' . $fotoNome);
                $fotoUrl = file_exists($fotoPath)
                    ? asset('storage/img-user/' . $fotoNome)
                    : asset('storage/img-user/padrao.png');
            @endphp

            <img src="{{ $fotoUrl }}" class="rounded-circle me-3"
                 width="70" height="70" style="object-fit: cover;">

            <div>
                <h5 class="mb-1">{{ $aluno->nome_a }}</h5>
                <p class="mb-0 text-muted small">
                    <strong>Turma:</strong> {{ $turma->serie_turma ?? '-' }} <br>
                    <strong>Matrícula:</strong> {{ $aluno->matricula }}
                </p>
            </div>
        </div>
    </div>

    {{-- 📋 Lista de ocorrências --}}
    @forelse($ocorrencias as $oc)
        @php
            $statusColors = [
                1 => 'success',   // ativa
                0 => 'secondary', // arquivada
                2 => 'danger',    // anulada
            ];
            $statusLabels = [
                1 => 'Ativa',
                0 => 'Arquivada',
                2 => 'Anulada',
            ];
        @endphp

        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Data:</strong> {{ $oc->created_at->format('d/m/Y H:i') }} <br>
                    <strong>Disciplina:</strong> {{ $oc->oferta->disciplina->descr_d ?? '-' }} <br>
                    <strong>Professor:</strong> {{ $oc->professor->usuario->nome_u ?? '-' }}
                </div>
                <span class="badge bg-{{ $statusColors[$oc->status] ?? 'secondary' }}">
                    {{ $statusLabels[$oc->status] ?? 'Desconhecido' }}
                </span>
            </div>

            <div class="card-body">
                
                @php
                    $motivos = '';
                    foreach ($oc->motivos as $index => $motivo) {
                        $motivos .= $motivo->descricao;
                        if ($index < count($oc->motivos) - 1) {
                            $motivos .= ' / ';
                        }
                    }
                @endphp


                {{-- 🧾 Motivos -}}
                <p class="mb-2"><strong>Motivos:</strong></p>--}}

                @php
                    // Monta os motivos em uma string separada por " / "
                    $motivos = $oc->motivos->pluck('descricao')->implode(' / ');
                    // Monta a descrição completa: descrição + motivos (se existirem)
                    $textoDescricao = trim(collect([$oc->descricao, $motivos])->filter()->implode(' / '));
                @endphp
                {{-- 🧾 Descrição --}}
                <p><strong>Descrição:</strong>
                    {{ $textoDescricao ?: '—' }}
                </p>


                {{--<ul>
                    @foreach($oc->motivos as $motivo)
                        <li>
                            <span class="badge bg-info text-dark mb-0" title="{{ $motivo->categoria }}">
                                {{ $motivo->descricao }}
                            </span>
                        </li>
                    @endforeach
                </ul>--}}

                {{-- 🏫 Outros campos -}}
                @if($oc->descricao)
                    @if($motivos)
                        <p><strong>Descrição:</strong> {{ $oc->descricao .' / '. $motivos }}</p>
                    @else
                        <p><strong>Descrição:</strong> {{ $oc->descricao }}</p>
                    @endif
                @else
                    @if($motivos)
                        <p><strong>Descrição:</strong> {{ $motivos }}</p>
                    @else
                        <p><strong>Descrição:</strong> {{ -- }}</p>
                    @endif
                @endif--}}

                @if($oc->local)
                    <p><strong>Local:</strong> {{ $oc->local }}</p>
                @endif

                @if($oc->atitude)
                    <p><strong>Atitude:</strong> {{ $oc->atitude }}</p>
                @endif

                @if($oc->comportamento)
                    <p><strong>Comportamento:</strong> {{ $oc->comportamento }}</p>
                @endif

                @if($oc->sugestao)
                    <p><strong>Sugestão:</strong> {{ $oc->sugestao }}</p>
                @endif
            </div>
        </div>

    @empty
        <div class="alert alert-secondary text-center">
            Nenhuma ocorrência registrada para este aluno.
        </div>
    @endforelse
</div>

{{-- 🎨 Estilos opcionais --}}
<style>
.card-header strong { font-weight: 600; }
.card-body ul { padding-left: 1.3rem; margin-bottom: 0.5rem; }
</style>

@endsection


@push('styles')
<style>
    .card-body p,
    .card-body ul,
    .card-body li,
    .card-body span.badge {
        margin: 0 !important;           /* remove margens acima e abaixo */
        padding: 0 !important;          /* remove espaçamentos internos extras */
        line-height: 1.1 !important;    /* aproxima as linhas */
    }

    .card-body ul {
        list-style: none;               /* remove marcadores, se quiser */
        padding-left: 0 !important;     /* remove recuo da lista */
    }

    .card-body li {
        display: inline-block;          /* coloca badges lado a lado */
        margin-right: 1px;              /* pequeno espaço horizontal entre badges */
        margin-bottom: 1px;             /* leve espaço entre linhas de badges */
    }

</style>
@endpush