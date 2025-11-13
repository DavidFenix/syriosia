@extends('layouts.app')

@section('content')
<div class="container">

    <h1>Resultado Final da Importação</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Linha</th>
                <th>Status</th>
                <th>Mensagem</th>
            </tr>
        </thead>

        <tbody>
            @foreach($resultado as $r)
                <tr class="{{ $r['status'] === 'sucesso' ? 'table-success' : 'table-danger' }}">
                    <td>{{ $r['linha'] }}</td>
                    <td>{{ ucfirst($r['status']) }}</td>
                    <td>{{ $r['msg'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('escola.professores.lote.index') }}" class="btn btn-primary">← Voltar</a>

</div>
@endsection
