@extends('layouts.app')

@section('content')
<div class="container">

    {{-- 🔙 Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>👩‍🏫 Alunos da Turma</h2>
        <a href="{{ route('professor.ofertas.index') }}" class="btn btn-secondary">⬅ Voltar às Ofertas</a>
    </div>

    {{-- 🏫 Informações da oferta --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-0">
                
                @if(config('app.debug'))
                    <strong>Disciplina/Turma/Ano:</strong> {{ $oferta->disciplina->descr_d ?? '—' }}::{{ $oferta->turma->serie_turma ?? '—' }}::{{ $oferta->ano_letivo }}
                    
                    <strong>oferta_id/oferta->school_id/oferta_turma_id/oferta_turma_school_id:{{ $oferta->id }} {{ $oferta->school_id }} {{ $oferta->turma->id }} {{ $oferta->turma->school_id }}</strong>
                @else
                    <strong>Disciplina/Turma/Ano:</strong> {{ $oferta->disciplina->descr_d ?? '—' }}::{{ $oferta->turma->serie_turma ?? '—' }}::{{ $oferta->ano_letivo }}
                @endif
                
                <!--br>
                <strong>Turma:</strong> {{ $oferta->turma->serie_turma ?? '—' }}
                <br>
                <strong>Ano Letivo:</strong> {{ $oferta->ano_letivo }}-->
            </h5>
        </div>
    </div>

    {{-- ⚙️ Filtros e ações --}}
    <form method="GET" action="{{ route('professor.ofertas.ocorrencias.create', $oferta->id) }}" id="formOcorrencias">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <input type="checkbox" id="checkAll" class="form-check-input me-1">
                <label for="checkAll" class="fw-semibold">Selecionar todos</label>
            </div>
            <button type="submit" class="btn btn-success">
                ✅ Aplicar Ocorrência
            </button>
        </div>


        {{-- 📋 Tabela de alunos --}}
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>

                    @if(config('app.debug'))
                        <th>aluno_id</th>
                        <th>aluno_school_id</th>
                        <th>oferta_id</th>
                        <th>oferta_school_id</th>
                        <th>oferta_turma_id</th>
                        <th>oferta_turma_school_id</th>
                    @endif
                                        
                    <th></th> {{-- checkbox --}}
                    <th>Foto</th>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Ocorrências Ativas</th>
                    <th>Total Geral</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alunos as $index => $a)
                    @php
                        $fotoNome = $a->matricula . '.png';
                        $fotoRelPath = 'storage/img-user/' . $fotoNome;
                        $fotoAbsoluta = public_path($fotoRelPath);
                        $fotoUrl = file_exists($fotoAbsoluta)
                            ? asset($fotoRelPath)
                            : asset('storage/img-user/padrao.png');
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        @if(config('app.debug'))
                            <td>{{ $a->id }}</td>
                            <td>{{ $a->school_id }}</td>
                            <td>{{ $oferta->id }}</td>
                            <td>{{ $oferta->school_id }}</td>
                            <td>{{ $oferta->turma->id }}</td>
                            <td>{{ $oferta->turma->school_id }}</td>
                        @endif

                        <td>
                            <input type="checkbox" name="alunos[]" value="{{ $a->id }}" class="form-check-input aluno-checkbox">
                        </td>
                        <td class="text-center">
                            <img src="{{ $fotoUrl }}" alt="Foto de {{ $a->nome_a }}" 
                                 class="rounded-circle" width="45" height="45"
                                 style="object-fit: cover; cursor: zoom-in;"
                                 onclick="abrirImagem('{{ $fotoUrl }}')">
                        </td>
                        <td>{{ $a->matricula }}</td>
                        <td>{{ $a->nome_a }}</td>
                        <td>
                            @php                                
                                $total = $a->total_ocorrencias_ativas ?? 0;
                                [$cor, $texto] = match (true) {
                                    $total == 0 => ['light', 'text-dark'],
                                    $total == 1 => ['secondary', 'text-white'],     // 👈 texto escuro para fundo claro
                                    $total == 2 => ['warning', 'text-dark'],   // idem
                                    $total == 3 => ['orange', 'text-white'],
                                    $total >= 4 => ['danger', 'text-white'],
                                    default => ['secondary', 'text-white'],
                                };
                            @endphp
                            <span class="badge bg-{{ $cor }} {{ $texto }}">{{ $total }}</span>
                        </td>
                        <td>
                            @php
                                $totalGeral = $a->total_ocorrencias_geral ?? 0;
                                [$corGeral, $textoGeral] = ['gray', 'text-dark'];
                            @endphp
                            <span class="badge bg-{{ $corGeral }} {{ $textoGeral }}">
                                {{ $totalGeral }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('professor.ocorrencias.historico', $a->id) }}" class="btn btn-outline-info btn-sm">
                                📜 Detalhes
                            </a>
                            <a href="{{ route('professor.ocorrencias.historico_resumido', $a->id) }}" class="btn btn-outline-info btn-sm">
                                📄 Resumo
                            </a>

                            <!--a href="#" class="btn btn-outline-secondary btn-sm">📄 PDF</a-->
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Nenhum aluno encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
</div>

{{-- 🔍 Modal para zoom da imagem --}}
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 text-center">
      <img id="zoomImage" src="" alt="Foto ampliada" class="img-fluid rounded shadow-lg">
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
.bg-orange {
    background-color: #ff9800 !important; /* 🔸 tom laranja forte */
    color: #fff !important;
}
.bg-gray {
    background-color: #adb5bd !important; /* 🔸 tom laranja forte */
    color: #000 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.aluno-checkbox').forEach(cb => cb.checked = this.checked);
});

function abrirImagem(src) {
    const img = document.getElementById('zoomImage');
    img.src = src;
    const modal = new bootstrap.Modal(document.getElementById('modalZoom'));
    modal.show();
}
</script>
@endpush