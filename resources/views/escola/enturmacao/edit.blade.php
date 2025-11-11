@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Enturmação</h1>

    {{-- mensagens flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- erros de validação --}}
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
        // fallback de segurança caso não venha do controller
        $anoLetivo = $anoLetivo ?? (session('ano_letivo_atual') ?? date('Y'));
    @endphp

    <div class="card">
        <div class="card-body">
            <form action="{{ route('escola.enturmacao.update', $enturmacao) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Aluno (somente leitura) --}}
                    <div class="col-md-6">
                        <label class="form-label">Aluno</label>
                        <input type="text" class="form-control" 
                               value="{{ $enturmacao->aluno->nome_a ?? '—' }} (Matrícula: {{ $enturmacao->aluno->matricula ?? '—' }})" 
                               disabled>
                    </div>

                    {{-- Ano letivo (somente leitura) --}}
                    <div class="col-md-3">
                        <label class="form-label">Ano letivo</label>
                        <input type="text" class="form-control" value="{{ $enturmacao->ano_letivo ?? $anoLetivo }}" disabled>
                        <input type="hidden" name="ano_letivo" value="{{ $enturmacao->ano_letivo ?? $anoLetivo }}">
                    </div>

                    {{-- Status vigente --}}
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vigente" name="vigente"
                                   value="1" {{ old('vigente', $enturmacao->vigente) ? 'checked' : '' }}>
                            <label class="form-check-label" for="vigente">
                                Vínculo vigente (ativo)
                            </label>
                        </div>
                    </div>

                    {{-- Troca de turma --}}
                    <div class="col-md-6">
                        <label class="form-label">Turma</label>
                        <select name="turma_id" class="form-select" required>
                            <option value="" disabled>— selecione a turma —</option>
                            @foreach($turmas as $turma)
                                <option value="{{ $turma->id }}"
                                    {{ old('turma_id', $enturmacao->turma_id) == $turma->id ? 'selected' : '' }}>
                                    {{ $turma->serie_turma }} — {{ $turma->turno }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            A alteração de turma afeta apenas o ano letivo {{ $enturmacao->ano_letivo ?? $anoLetivo }}.
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-success">Salvar alterações</button>
                    <a href="{{ route('escola.enturmacao.index') }}" class="btn btn-secondary">Cancelar</a>

                    {{-- Remover vínculo (apenas esta enturmação) --}}
                    <form action="{{ route('escola.enturmacao.destroy', $enturmacao) }}" method="POST" 
                          onsubmit="return confirm('Remover este vínculo de enturmação?');" class="ms-auto">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">🗑 Remover vínculo</button>
                    </form>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted small mt-3">
        Observação: aluno, escola e ano letivo são históricos e não devem ser alterados nesta tela.
    </p>
</div>
@endsection
