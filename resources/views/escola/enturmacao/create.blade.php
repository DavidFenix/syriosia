@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nova Enturmação</h1>

    @php
        $abaAtiva = request('aba', 'geral'); // por padrão: "geral"
    @endphp

    <ul class="nav nav-tabs" id="tabEnturmacao" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $abaAtiva === 'geral' ? 'active' : '' }}" data-bs-toggle="tab" href="#geral" role="tab">
                Pesquisa Geral
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $abaAtiva === 'turma' ? 'active' : '' }}" data-bs-toggle="tab" href="#turma" role="tab">
                Por Turma
            </a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <div class="tab-pane fade {{ $abaAtiva === 'geral' ? 'show active' : '' }}" id="geral" role="tabpanel">
            @include('escola.enturmacao._form_pesquisa_geral')
        </div>
        <div class="tab-pane fade {{ $abaAtiva === 'turma' ? 'show active' : '' }}" id="turma" role="tabpanel">
            @include('escola.enturmacao._form_pesquisa_turma')
        </div>
    </div>

</div>

{{-- pequeno script para marcar/desmarcar todos --}}
<script>
function toggleAll(checkbox, name) {
    document.querySelectorAll('input[name="'+name+'[]"]').forEach(cb => cb.checked = checkbox.checked);
}
</script>
@endsection







{{--
@extends('layouts.app')

    @section('content')
    <div class="container">
        <h1>Nova Enturmação</h1>

        {{-- mensagens flash -}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- erros de validação -}}
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Erros ao enviar o formulário:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $anoLetivo = session('ano_letivo_atual') ?? date('Y');
        @endphp

        <div class="card">
            <div class="card-body">
                <form action="{{ route('escola.enturmacao.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Aluno</label>
                            <select name="aluno_id" class="form-select" required>
                                <option value="" selected disabled>— selecione o aluno —</option>
                                @foreach($alunos as $aluno)
                                    <option value="{{ $aluno->id }}"
                                        {{ old('aluno_id') == $aluno->id ? 'selected' : '' }}>
                                        {{ $aluno->nome_a }} — Matrícula: {{ $aluno->matricula }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Turma</label>
                            <select name="turma_id" class="form-select" required>
                                <option value="" selected disabled>— selecione a turma —</option>
                                @foreach($turmas as $turma)
                                    <option value="{{ $turma->id }}"
                                        {{ old('turma_id') == $turma->id ? 'selected' : '' }}>
                                        {{ $turma->serie_turma }} — {{ $turma->turno }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ano letivo</label>
                            <input type="text" class="form-control" value="{{ $anoLetivo }}" disabled>
                            {{-- envia escondido para o store (caso queira usar) -}}
                            <input type="hidden" name="ano_letivo" value="{{ $anoLetivo }}">
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="vigente" name="vigente"
                                       value="1" {{ old('vigente', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="vigente">
                                    Vínculo vigente (ativo)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-success">Salvar</button>
                        <a href="{{ route('escola.enturmacao.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-muted small mt-3">
            Dica: este vínculo é salvo para o ano letivo <strong>{{ $anoLetivo }}</strong>.  
            Para históricos de anos anteriores, use o filtro na lista de enturmações.
        </p>
    </div>
    @endsection
--}}