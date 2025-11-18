@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-3 fw-bold">📥 Importar Ofertas (Lotação de Professores)</h2>

    <p class="text-muted">
        Use este módulo para cadastrar ofertas (professor ↔ disciplina ↔ turma) em lote.
    </p>

    <div class="alert alert-info">
        <strong>Passo a passo:</strong>
        <ol class="mb-0">
            <li>Baixe o modelo CSV.</li>
            <li>Preencha CPF do professor, <code>disciplina_id</code> e <code>turma_id</code>.</li>
            <li>Envie o arquivo para pré-visualização.</li>
        </ol>
    </div>

    <div class="mb-3">
        <a href="{{ route('escola.ofertas.lote.modelo') }}" class="btn btn-outline-primary">
            ⬇️ Baixar modelo CSV de ofertas
        </a>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">Enviar arquivo CSV</div>

        <div class="card-body">
            <form action="{{ route('escola.ofertas.lote.preview') }}"
                  method="POST"
                  enctype="multipart/form-data">

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
                        Formato: CSV separado por ponto e vírgula (<code>;</code>).
                    </small>
                </div>

                <button type="submit" class="btn btn-success">
                    🔎 Pré-visualizar Ofertas
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
