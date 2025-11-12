@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pré-visualização da Importação</h1>

    <form method="POST" action="{{ route('escola.professores.lote.importar') }}">
        @csrf

        <input type="hidden" name="dados"
               value="{{ htmlspecialchars(json_encode($preview), ENT_QUOTES, 'UTF-8') }}">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Linha</th>
                    <th>CPF</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                </tr>
            </thead>

            <tbody>
            @foreach($preview as $linha)
                @php
                    $class = $linha['importar']
                        ? 'table-success'
                        : ($linha['status'] === 'ignorado' ? 'table-warning' : 'table-danger');
                @endphp

                <tr class="{{ $class }}">
                    <td>{{ $linha['linha'] }}</td>
                    <td>{{ $linha['cpf'] }}</td>
                    <td>{{ $linha['nome'] }}</td>
                    <td>{{ ucfirst($linha['status']) }}</td>
                    <td>{{ $linha['msg'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <a href="{{ route('escola.professores.lote.index') }}" class="btn btn-secondary">
            ← Cancelar
        </a>

        @if( collect($preview)->where('importar', true)->count() > 0 )
            <button class="btn btn-primary">
                ✔ Confirmar Importação
            </button>
        @endif
    </form>

</div>
@endsection
