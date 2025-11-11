@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lotação de Professores — {{ $anoLetivo }}</h1>

    <form method="GET" class="mb-3">
        <label class="form-label">Professor</label>
        <div class="input-group">
            <select name="professor_id" class="form-select" onchange="this.form.submit()">
                <option value="">— Selecione —</option>
                @foreach($professores as $p)
                    <option value="{{ $p->id }}" {{ $professorSelecionado == $p->id ? 'selected' : '' }}>
                        {{ $p->usuario->nome_u ?? 'Professor sem usuário' }}
                    </option>
                @endforeach
            </select>
            @if($professorSelecionado)
                <a href="{{ route('escola.lotacao.create', ['professor_id' => $professorSelecionado]) }}" class="btn btn-success">➕ Nova Oferta</a>
            @endif
        </div>
    </form>

    <div class="mb-3"><a href="{{ route('escola.lotacao.diretor_turma.index') }}" class="btn btn-success">Gerenciar Diretor de Turma</a></div>

    @if($professorSelecionado)
        @forelse($ofertas as $disciplinaId => $grupo)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $grupo->first()->disciplina->descr_d ?? 'Disciplina desconhecida' }}</strong>
                    <a href="{{ route('escola.lotacao.edit', ['lotacao' => $disciplinaId, 'professor_id' => $professorSelecionado]) }}" class="btn btn-sm btn-warning">✏️ Editar</a>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @foreach($grupo as $of)
                            <li>{{ $of->turma->serie_turma ?? '-' }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <div class="alert alert-info">Nenhuma oferta encontrada para este professor.</div>
        @endforelse
    @endif


</div>
@endsection
