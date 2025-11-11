@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nova Oferta</h1>

    <form method="POST" action="{{ route('escola.lotacao.store') }}">
        @csrf
        <input type="hidden" name="professor_id" value="{{ $professorId }}">

        <div class="mb-3">
            <label class="form-label">Disciplina</label>
            <select name="disciplina_id" class="form-select" required>
                <option value="">— Selecione —</option>
                @foreach($disciplinas as $d)
                    <option value="{{ $d->id }}">{{ $d->descr_d }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Turmas</label>
            @foreach($turmas as $t)
                <div class="form-check">
                    <input type="checkbox" name="turmas[]" value="{{ $t->id }}" id="turma_{{ $t->id }}" class="form-check-input">
                    <label for="turma_{{ $t->id }}" class="form-check-label">
                        {{ $t->serie_turma }} — {{ $t->turno }}
                    </label>
                </div>
            @endforeach
        </div>

        <button class="btn btn-success">Salvar</button>
        <a href="{{ route('escola.lotacao.index', ['professor_id' => $professorId]) }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
