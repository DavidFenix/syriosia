@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">📥 Importar Alunos em Lote</h2>
    <p class="text-muted">
        Use este módulo para cadastrar e enturmar vários alunos de uma vez, usando um arquivo CSV.
    </p>

    <div class="alert alert-info">
        <strong>Passo a passo:</strong>
        <ol class="mb-0">
            <li>Baixe o modelo de arquivo CSV com as turmas da escola.</li>
            <li>Preencha <code>matricula</code>, <code>nome</code> e escolha o <code>turma_id</code> adequado.</li>
            <li>Envie o arquivo abaixo para pré-visualizar as linhas antes de importar.</li>
        </ol>
    </div>

    <div class="mb-3">
        <a href="{{ route('escola.alunos.lote.modelo') }}" class="btn btn-outline-primary">
            ⬇️ Baixar modelo CSV de alunos
        </a>
    </div>

    @if($turmas->count())
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                Turmas disponíveis nesta escola
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID (turma_id)</th>
                                <th>Série / Turma</th>
                                <th>Turno</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($turmas as $t)
                            <tr>
                                <td>{{ $t->id }}</td>
                                <td>{{ $t->serie_turma }}</td>
                                <td>{{ $t->turno }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">
                    Use esses códigos de <strong>turma_id</strong> ao preencher o CSV.
                </small>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Nenhuma turma cadastrada nesta escola. Cadastre as turmas antes de usar a importação em lote.
        </div>
    @endif

    <div class="card">
        <div class="card-header fw-semibold">Enviar arquivo para pré-visualização</div>
        <div class="card-body">
            <form action="{{ route('escola.alunos.lote.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Arquivo CSV</label>
                    <input type="file"
                           name="arquivo"
                           class="form-control @error('arquivo') is-invalid @enderror"
                           accept=".csv,text/csv">
                    @error('arquivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Formato: CSV separado por ponto e vírgula (<code>;</code>). Máx. 4MB.
                    </small>
                </div>

                <button type="submit" class="btn btn_success btn btn-success">
                    🔎 Pré-visualizar alunos
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


{{--
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">📥 Importar Alunos em Lote</h2>
    <p class="text-muted">
        Use este módulo para cadastrar e enturmar vários alunos de uma vez, usando um arquivo CSV.
    </p>

    <div class="alert alert-info">
        <strong>Passo a passo:</strong>
        <ol class="mb-0">
            <li>Baixe o modelo de arquivo CSV com as turmas da escola.</li>
            <li>Preencha <code>matricula</code>, <code>nome</code> e escolha o <code>turma_id</code> adequado.</li>
            <li>Envie o arquivo abaixo para pré-visualizar as linhas antes de importar.</li>
        </ol>
    </div>

    <div class="mb-3">
        <a href="{{ route('escola.alunos.lote.modelo') }}" class="btn btn-outline-primary">
            ⬇️ Baixar modelo CSV de alunos
        </a>
    </div>

    @if($turmas->count())
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                Turmas disponíveis nesta escola
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID (turma_id)</th>
                                <th>Série / Turma</th>
                                <th>Turno</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($turmas as $t)
                            <tr>
                                <td>{{ $t->id }}</td>
                                <td>{{ $t->serie_turma }}</td>
                                <td>{{ $t->turno }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">
                    Use esses códigos de <strong>turma_id</strong> ao preencher o CSV.
                </small>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Nenhuma turma cadastrada nesta escola. Cadastre as turmas antes de usar a importação em lote.
        </div>
    @endif

    <div class="card">
        <div class="card-header fw-semibold">Enviar arquivo para pré-visualização</div>
        <div class="card-body">
            <form action="{{ route('escola.alunos.lote.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Arquivo CSV</label>
                    <input type="file"
                           name="arquivo"
                           class="form-control @error('arquivo') is-invalid @enderror"
                           accept=".csv,text/csv">
                    @error('arquivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Formato: CSV separado por ponto e vírgula (<code>;</code>). Máx. 4MB.
                    </small>
                </div>

                <button type="submit" class="btn btn-success">
                    🔎 Pré-visualizar alunos
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
--}}