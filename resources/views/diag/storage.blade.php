@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Diagnóstico de Storage</h2>
    <p>Gerado em {{ now() }}</p>

    <ul class="list-group mt-3">
        <li class="list-group-item">
            <strong>Symlink public/storage existe:</strong>
            {!! $results['symlink_exists'] ? '✅ SIM' : '❌ NÃO' !!}
        </li>

        <li class="list-group-item">
            <strong>Symlink aponta para:</strong>
            {{ $results['symlink_points_to'] ?? 'n/d' }}
        </li>

        <li class="list-group-item">
            <strong>storage/app/public existe:</strong>
            {!! $results['storage_path_exists'] ? '✅ SIM' : '❌ NÃO' !!}
        </li>

        <li class="list-group-item">
            <strong>Raiz do disco "public":</strong>
            {{ $results['disk_root'] }}
        </li>

        <li class="list-group-item">
            <strong>Teste de escrita:</strong>
            {!! $results['write_test'] ? '✅ OK' : '❌ FALHOU' !!}
        </li>

        <li class="list-group-item">
            <strong>Teste de leitura:</strong>
            {!! $results['read_test'] ? '✅ OK' : '❌ FALHOU' !!}
        </li>

        <li class="list-group-item">
            <strong>Teste de deleção:</strong>
            {!! $results['delete_test'] ? '✅ OK' : '❌ FALHOU' !!}
        </li>

        @if(!empty($results['errors']))
            <li class="list-group-item list-group-item-danger">
                <strong>Erros:</strong>
                <pre>{{ print_r($results['errors'], true) }}</pre>
            </li>
        @endif
    </ul>

    <a href="{{ route('diag.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
