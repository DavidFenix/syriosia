@extends('layouts.app')

@section('content')
<div class="container">

    <h1 class="mb-4">📦 Resultado da Importação</h1>

    <div class="table-responsive mt-4">
        <table class="table table-bordered table-sm text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>

            @foreach($resultado as $i => $linha)
                @php
                    $classe = $linha['status'] === 'sucesso' ? 'table-success' :
                             ($linha['status'] === 'erro' ? 'table-danger' : 'table-warning');

                    $icone = $linha['status'] === 'sucesso' ? '✔️' :
                             ($linha['status'] === 'erro' ? '❌' : '🟡');
                @endphp

                <tr class="{{ $classe }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $icone }} {{ strtoupper($linha['status']) }}</td>
                    <td>{{ $linha['msg'] }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('escola.professores.lote.index') }}" class="btn btn-primary">
            ⬅ Voltar ao Cadastro em Lote
        </a>
    </div>

</div>
@endsection
