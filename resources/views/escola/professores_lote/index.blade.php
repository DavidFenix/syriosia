@extends('layouts.app')

@section('content')
<div class="container">

    <h1 class="mb-4">📁 Cadastro de Professores em Lote</h1>

    <p class="mb-3">
        Envie um arquivo <strong>.csv</strong> contendo as colunas:
        <code>cpf;nome;role</code>.
        Apenas role <strong>professor</strong> é permitida.
    </p>

    <a href="{{ route('escola.professores.lote.modelo') }}" class="btn btn-outline-primary mb-4">
        ⬇ Baixar Modelo CSV
    </a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro:</strong> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('escola.professores.lote.preview') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Selecione o arquivo CSV:</label>
            <input type="file" name="arquivo" accept=".csv,.txt" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">
            📊 Pré-visualizar
        </button>
    </form>

</div>
@endsection

{{--
@extends('layouts.app')

@section('content')
<div class="container">

    <h1>Cadastro em Lote de Professores (CSV)</h1>

    <div class="alert alert-info">
        <b>Instruções:</b><br>
        • Envie um arquivo .csv separado por ponto-e-vírgula (;) - (Excel faz isso automaticamente) basta salvar no modelo .csv<br>
        • A acentuação deve ser mantida normalmente (UTF-8 ou ISO)<br>
        • Nada será salvo imediatamente — você poderá revisar antes<br>
        • Senha inicial = CPF<br>
        • Apenas role “professor” será aceita<br>
    </div>

    <a href="{{ route('escola.professores.lote.modelo') }}" class="btn btn-success mb-3">
        📄 Baixar Modelo CSV
    </a>

    <form action="{{ route('escola.professores.lote.preview') }}" method="POST"
          enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Arquivo CSV:</label>
            <input type="file" name="arquivo" class="form-control" required>
        </div>

        <button class="btn btn-primary">🔍 Pré-visualizar</button>

    </form>

</div>
@endsection
--}}

