@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Oferta: {{ $disciplina->descr_d }}</h1>

    <form method="POST" action="{{ route('escola.lotacao.update', $disciplina->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="professor_id" value="{{ $professorId }}">

        <div class="mb-3">
            <label class="form-label">Turmas</label>
            @foreach($turmas as $t)
                <div class="form-check">
                    <input type="checkbox" name="turmas[]" value="{{ $t->id }}"
                           id="turma_{{ $t->id }}" class="form-check-input"
                           {{ in_array($t->id, $turmasAtuais) ? 'checked' : '' }}>
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
