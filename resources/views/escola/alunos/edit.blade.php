@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Aluno</h1>

    <form action="{{ route('escola.alunos.update', $aluno->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label class="form-label">Nome do Aluno</label>
            <input type="text" name="nome_a" class="form-control"
                   value="{{ old('nome_a', $aluno->nome_a) }}"
                   {{ $isNativo ? '' : 'readonly' }}>
        </div>

        {{-- Matrícula (sempre protegida) --}}
        <div class="mb-3">
            <label class="form-label">Matrícula</label>
            <input type="text" name="matricula" class="form-control"
                   value="{{ $aluno->matricula }}" readonly>
        </div>

        {{-- Troca de turma --}}
        <div class="mb-3">
            <label class="form-label">Turma</label>
            <select name="turma_id" class="form-select">
                <option value="">— Nenhuma turma —</option>
                @foreach($turmas as $t)
                    <option value="{{ $t->id }}"
                        @if(optional($aluno->enturmacao->first())->turma_id == $t->id) selected @endif>
                        {{ $t->serie_turma }} — {{ $t->turno }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-success">💾 Salvar alterações</button>
        <a href="{{ route('escola.alunos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection



{{--
@extends('layouts.app')

    @section('content')
    <div class="container">
        <h1>Editar Aluno</h1>
        <form action="{{ route('escola.alunos.update', $aluno) }}" method="post">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome_a" class="form-control" value="{{ $aluno->nome_a }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Matrícula</label>
                <input type="text" name="matricula" class="form-control" value="{{ $aluno->matricula }}" required>
            </div>
            <button class="btn btn-success">Atualizar</button>
            <a href="{{ route('escola.alunos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    @endsection
    --}}
