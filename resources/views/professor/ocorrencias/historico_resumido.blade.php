@extends('layouts.app')

@section('content')
<div class="container py-4" id="historicoOcorrencias">

    @include('components.pdf_header')

    {{-- ===================== CABEÇALHO DINÂMICO DA ESCOLA ===================== -}}
    <div class="text-center mb-4">
        @if($escola->logo_path && file_exists(public_path('storage/'.$escola->logo_path)))
          <img src="{{ asset('storage/'.$escola->logo_path) }}" 
               alt="Logo da Escola"
               style="width:80px; height:80px; object-fit:contain; border-radius:8px;">
          <h4 class="mb-1">{{ $escola->nome_e ?? 'Nome da Escola' }}</h4>
          <p class="text-muted fst-italic small">
                "{{ $escola->frase_efeito ?? '' }}"
          </p>
        @else
          <img src="{{ asset('storage/img-user/padrao.png') }}" 
               alt="Logo padrão"
               style="width:80px; height:80px; object-fit:contain; border-radius:8px;">
          <h4 class="mb-1">{{ $escola->nome_e ?? 'Nome da Escola' }}</h4>
          <p class="text-muted fst-italic small">
                "{{ $escola->frase_efeito ?? '' }}"
          </p>
        @endif
        <hr class="mt-3 mb-4">
    </div>

    {{-- ===================== CABEÇALHO DA ESCOLA ===================== -}}
    <div class="text-center mb-4">
        <img src="{{ asset('storage/img-user/logo1_ubiratan.png') }}" width="60" height="60"
             class="rounded-circle mb-2" alt="Logo da escola">
        <h4 class="mb-1">{{ $escola->nome_e ?? 'Nome da Escola' }}</h4>
        <p class="text-muted fst-italic small">
            "{{ $escola->frase_efeito ?? 'Educar é transformar vidas.' }}"
        </p>
        <hr class="mt-3 mb-4">
    </div>
    --}}

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
                    
                    @if(config('app.debug'))
                        <strong>turma_id:</strong> {{ $turma->id }} <br>
                    @endif
                    
                    <strong>Turma:</strong> {{ $turma->serie_turma ?? '-' }} <br>
                    <strong>Matrícula:</strong> {{ $aluno->matricula }}
                </p>
            </div>
        </div>
    </div>

    {{-- ===================== TÍTULO ===================== --}}
    <h5 class="text-center mb-3 fw-bold">📋 Histórico de Ocorrências do Aluno</h5>

    {{-- ===================== TABELA ===================== --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle table-striped text-center">
            <thead class="table-light text-center">
                <tr>
                    <th>#</th>
                    
                    @if(config('app.debug'))
                        <th>oco_id</th>
                        <th>oco_school_id</th>
                        <th>oco_aluno_id</th>
                        <th>aluno_school_id</th>
                        <th>oco_oferta_id</th>
                        <th>oco_oferta_turma_id</th>
                    @endif

                    <th>Data</th>
                    <th>Descrição / Motivos</th>
                    <th>Disciplina</th>
                    <th>Professor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ocorrencias as $i => $oc)
                    @php
                        $motivos = $oc->motivos->pluck('descricao')->join(' / ');
                        $status = match($oc->status) {
                            0 => ['Arquivada', 'secondary'],
                            1 => ['Ativa', 'success'],
                            2 => ['Anulada', 'danger'],
                            default => ['Desconhecido', 'dark']
                        };

                        $nome = $oc->professor->usuario->nome_u ?? '';
                        $partes = explode(' ', trim($nome));
                        $primeiro = $partes[0] ?? '';
                        $ultimo = count($partes) > 1 ? end($partes) : '';

                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>

                        @if(config('app.debug'))
                            <td class="text-center">{{ $oc->id }}</td>
                            <td class="text-center">{{ $oc->school_id }}</td>
                            <td class="text-center">{{ $oc->aluno_id }}</td>
                            <td class="text-center">{{ $aluno->school_id }}</td>
                            <td class="text-center">{{ $oc->oferta->id }}</td>
                            <td class="text-center">{{ $oc->oferta->turma->id }}</td>
                        @endif

                        <td>{{ $oc->created_at->format('d/m/Y') }}</td>
                        <td>{{ $oc->descricao }} 
                            @if($motivos) <span class="text-muted">/ {{ $motivos }}</span> @endif
                        </td>
                        <td class="text-center">{{ $oc->oferta->disciplina->abr ?? '-' }}</td>
                        <td>{{ $primeiro }} {{ $ultimo }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $status[1] }}">{{ $status[0] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Nenhuma ocorrência registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===================== BOTÕES DE AÇÃO ===================== --}}
    <div class="text-center mt-4 no-print">
        <button class="btn btn-outline-primary me-2" onclick="window.print()">🖨️ Imprimir</button>
        <a href="{{ route('professor.ocorrencias.pdf', $aluno->id) }}" class="btn btn-outline-danger">
            📥 Baixar PDF
        </a>
    </div>


</div>

{{-- ===================== ESTILOS DE IMPRESSÃO ===================== --}}
<style>
    @media print {
        body { background: white !important; }
        .no-print { display: none !important; }
        .card, .btn, nav, footer { display: none !important; }
        table { font-size: 0.9rem; }
        h4, h5 { color: black !important; }
    }
</style>

@endsection


