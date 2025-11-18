@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3 fw-bold">📥 Importar Turmas em Lote</h2>

    <p class="text-muted">
        Use este módulo para cadastrar várias turmas de uma vez, usando um arquivo CSV simples.
    </p>

    <div class="alert alert-info">
        <strong>Passo a passo:</strong>
        <ol class="mb-0">
            <li>Baixe o modelo de arquivo CSV.</li>
            <li>Preencha <code>serie_turma</code> e <code>turno</code> para cada turma.</li>
            <li>Envie o arquivo abaixo para pré-visualizar antes de importar.</li>
        </ol>
        <p class="mb-0 mt-2">
            <strong>Atenção:</strong> dentro da mesma escola não pode haver
            <code>serie_turma + turno</code> repetidos. Duplicatas serão ignoradas.
        </p>
    </div>

    <div class="mb-3">
        <a href="{{ route('escola.turmas.lote.modelo') }}" class="btn btn-outline-primary">
            ⬇️ Baixar modelo CSV de turmas
        </a>
    </div>

    @if($turmas->count())
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                Turmas já cadastradas nesta escola
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Série/Turma</th>
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
                    Essas combinações de <strong>serie_turma</strong> + <strong>turno</strong> já existem
                    e serão ignoradas caso apareçam no CSV.
                </small>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Nenhuma turma cadastrada nesta escola ainda.
        </div>
    @endif

    <div class="card">
        <div class="card-header fw-semibold">Enviar arquivo para pré-visualização</div>
        <div class="card-body">
            <form action="{{ route('escola.turmas.lote.preview') }}" method="POST" enctype="multipart/form-data">
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
                        Formato: CSV separado por ponto e vírgula (<code>;</code>). Máx. 4MB.<br>
                        Se houver uma linha de cabeçalho <code>serie_turma;turno</code>, ela será ignorada automaticamente.
                    </small>
                </div>

                <button type="submit" class="btn btn-success">
                    🔎 Pré-visualizar turmas
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
