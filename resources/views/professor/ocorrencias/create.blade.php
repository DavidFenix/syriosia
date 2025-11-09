@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h2 class="mb-4">
        📝 Aplicar Ocorrência — {{ $oferta->disciplina->descr_d ?? 'Disciplina não definida' }}
         — {{ $oferta->turma->serie_turma ?? '-' }} | {{ $anoLetivo }}
    </h2>

    {{-- 🔙 Voltar -}}
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">⬅ Voltar</a>

    {{-- 🧾 Informações de retorno --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('professor.ofertas.ocorrencias.store') }}" method="POST">
        @csrf

        {{-- 🔒 Identificadores fixos --}}
        <input type="hidden" name="oferta_id" value="{{ $oferta->id }}">
        @foreach($alunos as $aluno)
            <input type="hidden" name="alunos[]" value="{{ $aluno->id }}">
        @endforeach

        {{-- 🧍 Lista de alunos selecionados --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">👥 Alunos Selecionados</div>
            <ul class="list-group list-group-flush">
                
                @forelse($alunos as $a)
                    @php
                        $fotoNome = $a->matricula . '.png';
                        $fotoRelPath = 'storage/img-user/' . $fotoNome;
                        $fotoAbsoluta = public_path($fotoRelPath);
                        $fotoUrl = file_exists($fotoAbsoluta)
                            ? asset($fotoRelPath)
                            : asset('storage/img-user/padrao.png');
                    @endphp
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <img src="{{ $fotoUrl }}" alt="Foto de {{ $a->nome_a }}" 
                                 class="rounded-circle" width="40" height="40"
                                 style="object-fit: cover; cursor: zoom-in;"
                                 onclick="abrirImagem('{{ $fotoUrl }}')">
                        <span class="fw-semibold">{{ $a->nome_a }}</span>
                        <span class="text-muted">({{ $a->matricula }})</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center">Nenhum aluno selecionado</li>
                @endforelse
            </ul>
        </div>

        {{-- ⚙️ Motivos pré-definidos --}}
        <div class="accordion mb-4" id="accordionMotivos">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingMotivos">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMotivos">
                        ⚡ Motivos da Ocorrência
                    </button>
                </h2>
                <div id="collapseMotivos" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="row">
                            @foreach($motivos->groupBy('categoria') as $categoria => $lista)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <h6 class="fw-bold text-primary">{{ $categoria ?? 'Geral' }}</h6>
                                    @foreach($lista as $motivo)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="motivos[]" id="motivo{{ $motivo->id }}" 
                                                   value="{{ $motivo->id }}">
                                            <label class="form-check-label" for="motivo{{ $motivo->id }}">
                                                {{ $motivo->descricao }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🧠 Campo de descrição livre --}}
        <div class="mb-3">
            <label for="descricao" class="form-label fw-semibold">📝 Descrição livre (opcional)</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="3" placeholder="Descreva brevemente o ocorrido..."></textarea>
        </div>

        {{-- 🏫 Local da ocorrência --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="local" class="form-label fw-semibold">📍 Local</label>
                <select name="local" id="local" class="form-select">
                    <option value="Sala de aula">Sala de aula</option>
                    <option value="Ambientes de apoio">Ambientes de apoio</option>
                    <option value="Pátio da escola">Pátio da escola</option>
                    <option value="Quadra poliesportiva">Quadra poliesportiva</option>
                    <option value="Galerias">Galerias</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>

            {{-- ⚖️ Atitude do professor --}}
            <div class="col-md-4">
                <label for="atitude" class="form-label fw-semibold">👩‍🏫 Atitude do professor</label>
                <select name="atitude" id="atitude" class="form-select">
                    <option value="Advertência">Advertência</option>
                    <option value="Ordem de saída de sala">Ordem de saída de sala</option>
                    <option value="Outra">Outra</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="outra_atitude" class="form-label fw-semibold">🗒 Outra atitude (opcional)</label>
                <input type="text" name="outra_atitude" id="outra_atitude" class="form-control" placeholder="Descreva outra atitude, se houver">
            </div>
        </div>

        {{-- 😐 Comportamento --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="comportamento" class="form-label fw-semibold">🎭 Comportamento</label>
                <select name="comportamento" id="comportamento" class="form-select">
                    <option value="1ª vez">1ª vez</option>
                    <option value="Reincidente (pouco frequente)">Reincidente (pouco frequente)</option>
                    <option value="Reincidente (frequente)">Reincidente (frequente)</option>
                </select>
            </div>

            <div class="col-md-8">
                <label for="sugestao" class="form-label fw-semibold">💡 Sugestão de medidas a serem tomadas</label>
                <textarea name="sugestao" id="sugestao" class="form-control" rows="2" placeholder="Ex: Encaminhar à direção, conversar com os pais, etc."></textarea>
            </div>
        </div>

        {{-- 🚀 Botões de ação --}}
        <div class="text-end">
            <a href="{{ route('professor.ofertas.index') }}" class="btn btn-secondary">⬅ Voltar</a>
            <button type="submit" class="btn btn-success">✅ Registrar Ocorrência</button>
        </div>

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
    .accordion-button:not(.collapsed) {
        color: #0d6efd;
        background-color: #e7f1ff;
    }
    .list-group-item img {
        object-fit: cover;
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


{{--
@extends('layouts.app')

    @section('content')
    <div class="container py-3">
        <h2 class="mb-4">📝 Aplicar Ocorrência</h2>

        {{-- 🔙 Voltar -}}
        <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">⬅ Voltar</a>

        {{-- ⚙️ Formulário principal -}}
        <form action="{{ route('professor.ocorrencias.store') }}" method="POST">
            @csrf

            {{-- 🧾 Informações básicas -}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">🎯 Informações gerais</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alunos selecionados</label>
                        <div class="border rounded p-2 bg-light">
                            @forelse($alunos as $a)
                                <span class="badge bg-primary m-1">{{ $a->nome_a }}</span>
                                <input type="hidden" name="alunos[]" value="{{ $a->id }}">
                            @empty
                                <p class="text-muted">Nenhum aluno selecionado.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Disciplina</label>
                        <input type="text" class="form-control" value="{{ $oferta->disciplina->descr_d ?? '-' }}" disabled>
                        <input type="hidden" name="oferta_id" value="{{ $oferta->id }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Turma</label>
                        <input type="text" class="form-control" value="{{ $oferta->turma->serie_turma ?? '-' }}" disabled>
                    </div>
                </div>
            </div>

            {{-- 📋 Motivos da ocorrência -}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none fw-semibold w-100 text-start" 
                            type="button" data-bs-toggle="collapse" data-bs-target="#motivosCollapse"
                            aria-expanded="true" aria-controls="motivosCollapse">
                        📌 Escolher motivo(s) da ocorrência
                    </button>
                </div>
                <div id="motivosCollapse" class="collapse show">
                    <div class="card-body">
                        @forelse($motivos as $motivo)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="motivos[]" value="{{ $motivo->id }}" id="motivo{{ $motivo->id }}">
                                <label class="form-check-label" for="motivo{{ $motivo->id }}">
                                    {{ $motivo->descr_r }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted">Nenhum motivo cadastrado.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 🗒 Outra descrição -}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">🖊 Descrição adicional</h5>
                    <textarea name="descricao_extra" class="form-control" rows="3"
                              placeholder="Descreva a situação, se necessário..."></textarea>
                </div>
            </div>

            {{-- ⚙️ Detalhes complementares -}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">⚙️ Informações complementares</h5>

                    <div class="row g-3">
                        {{-- Local -}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Local</label>
                            <select name="local" class="form-select">
                                <option value="Sala de aula" selected>Sala de aula</option>
                                <option value="Ambientes de apoio">Ambientes de apoio</option>
                                <option value="Pátio da escola">Pátio da escola</option>
                                <option value="Quadra poliesportiva">Quadra poliesportiva</option>
                                <option value="Galerias">Galerias</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        {{-- Atitude -}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Atitude do professor</label>
                            <select name="atitude" class="form-select">
                                <option value="Advertência" selected>Advertência</option>
                                <option value="Ordem de saída de sala">Ordem de saída de sala</option>
                                <option value="Outra">Outra</option>
                            </select>
                        </div>

                        {{-- Outra atitude -}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Outra atitude (opcional)</label>
                            <input type="text" name="outra_atitude" class="form-control"
                                   placeholder="Descreva outra atitude, se necessário">
                        </div>

                        {{-- Comportamento -}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Comportamento do aluno</label>
                            <select name="comportamento" class="form-select">
                                <option value="1ª vez" selected>1ª vez</option>
                                <option value="Reincidente (pouco frequente)">Reincidente (pouco frequente)</option>
                                <option value="Reincidente (frequente)">Reincidente (frequente)</option>
                            </select>
                        </div>

                        {{-- Sugestão de medidas -}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Sugestão de medidas</label>
                            <textarea name="sugestao" class="form-control" rows="2"
                                      placeholder="Sugestões de medidas que a escola pode adotar"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ Botões -}}
            <div class="text-end">
                <button type="submit" class="btn btn-success">✅ Aplicar Ocorrência</button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <style>
        .card { border-radius: 1rem; }
        .card-header button { color: #333; }
        .form-check-label { user-select: none; }
    </style>
    @endsection
--}}