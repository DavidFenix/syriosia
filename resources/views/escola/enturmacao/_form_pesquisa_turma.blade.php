<form method="GET" action="{{ route('escola.enturmacao.create') }}" class="mb-4">
    <input type="hidden" name="aba" value="turma">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Ano origem</label>
            <input type="number" name="ano_origem" class="form-control" value="{{ request('ano_origem', date('Y')-1) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Turma origem</label>
            <select name="turma_origem" class="form-select">
                <option value="" disabled selected>— Selecione —</option>
                @foreach($turmas as $t)
                    <option value="{{ $t->id }}" {{ request('turma_origem') == $t->id ? 'selected' : '' }}>
                        {{ $t->serie_turma }} — {{ $t->turno }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">🔍 Buscar</button>
        </div>
    </div>
</form>

@if(isset($alunosTurmaOrigem) && $alunosTurmaOrigem->count())
<form method="POST" action="{{ route('escola.enturmacao.storeBatch') }}">
    @csrf
    <div class="card">
        <div class="card-header">
            <label><input type="checkbox" onclick="toggleAll(this, 'alunos')"> Marcar todos</label>
        </div>
        <ul class="list-group list-group-flush" style="max-height:400px;overflow-y:auto;">
            @foreach($alunosTurmaOrigem as $a)
                <li class="list-group-item">
                    <label>
                        <input type="checkbox" name="alunos[]" value="{{ $a->id }}">
                        {{ $a->nome_a }} <small class="text-muted">(Matrícula: {{ $a->matricula }})</small>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-3">
            <label class="form-label">Ano destino</label>
            <input type="number" name="ano_letivo" class="form-control" value="{{ session('ano_letivo_atual') ?? date('Y') }}">
        </div>
        <div class="col-md-5">
            <label class="form-label">Turma destino</label>
            <select name="turma_id" class="form-select" required>
                <option value="" disabled selected>— Selecione —</option>
                @foreach($turmas as $t)
                    <option value="{{ $t->id }}">{{ $t->serie_turma }} — {{ $t->turno }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-success w-100">✅ Enturmar Selecionados</button>
        </div>
    </div>
</form>
@endif
