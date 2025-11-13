@extends('layouts.app')

@section('content')
<div class="container">

    <h1 class="mb-4">📋 Pré-visualização da Importação</h1>

    <div class="alert alert-info">
        Confira os dados antes de confirmar. Linhas com erro não serão importadas.
    </div>

    @php
        $total = count($linhas);
        $importarCount = collect($linhas)->where('importar', true)->count();
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <strong>Total de linhas:</strong> {{ $total }} <br>
            <strong>A serem importadas:</strong>
                <span class="text-success">{{ $importarCount }}</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>Linha</th>
                    <th>CPF</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>

            @foreach($linhas as $linha)
                @php
                    $classe = match($linha['status']) {
                        'ok'       => 'table-success',
                        'info'     => 'table-primary',
                        'ignorado' => 'table-warning',
                        'erro'     => 'table-danger',
                        default    => '',
                    };

                    $icone = match($linha['status']) {
                        'ok'       => '✔️',
                        'info'     => '🔵',
                        'ignorado' => '🟡',
                        'erro'     => '❌',
                        default    => '',
                    };
                @endphp

                <tr class="{{ $classe }}">
                    <td>{{ $linha['linha'] }}</td>
                    <td>{{ $linha['cpf'] }}</td>
                    <td>{{ $linha['nome'] }}</td>
                    <td>{{ $icone }} {{ strtoupper($linha['status']) }}</td>
                    <td>{{ $linha['msg'] }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">

        <a href="{{ route('escola.professores.lote.index') }}" class="btn btn-secondary">
            ⬅ Voltar
        </a>

        @if($importarCount > 0)
            <form action="{{ route('escola.professores.lote.importar') }}" method="POST">
                @csrf
                <input type="hidden" name="linhas" value="{{ base64_encode(json_encode($linhas)) }}">
                <button type="submit" class="btn btn-success">
                    ✔ Confirmar Importação
                </button>
            </form>
        @else
            <button class="btn btn-secondary" disabled>
                Nenhuma linha válida para importar
            </button>
        @endif

    </div>

</div>
@endsection
