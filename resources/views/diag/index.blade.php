@extends('layouts.app')

@section('content')
<div class="container">
    <h1>🩺 Diagnóstico do Ambiente</h1>
    <p class="text-muted">Gerado em {{ now()->format('Y-m-d H:i:s') }}</p>
    <hr>

    {{-- ===========================
         STATUS GERAL
    ============================ --}}
    <h3>✅ Status Geral</h3>
    <ul class="list-group mb-4">
        <li class="list-group-item">
            <strong>Railway:</strong>
            @if($status['railway']) ✅ Ativo @else ❌ Não detectado @endif
        </li>

        <li class="list-group-item">
            <strong>HTTPS:</strong>
            @if($status['https']) ✅ Detectado @else ⚠️ Não detectado @endif
        </li>

        <li class="list-group-item">
            <strong>Cookie de Teste:</strong>
            @if($status['cookie_received']) ✅ Recebido @else ⚠️ Ainda não recebido @endif
        </li>

        <li class="list-group-item">
            <strong>SESSION_SECURE_COOKIE:</strong>
            @php $secure = config('session.secure'); @endphp

            @if($secure === true)
                ✅ true
            @elseif($secure === false)
                ⚠️ false
            @else
                null
            @endif
        </li>

        <li class="list-group-item">
            <strong>APP_ENV:</strong>
            @if($status['env'] === 'production')
                ✅ production
            @else
                ⚠️ {{ $status['env'] }}
            @endif
        </li>
    </ul>

    {{-- ===========================
         VARIÁVEIS DE AMBIENTE
    ============================ --}}
    <h3>🌐 Variáveis de Ambiente (mascaradas)</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-sm small">
            <thead><tr><th>Chave</th><th>Valor</th></tr></thead>
            <tbody>
                @foreach($env as $key => $value)
                    <tr>
                        <td><code>{{ $key }}</code></td>
                        <td>{{ $value === null ? 'null' : $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <hr>

    {{-- ===========================
         CONFIGURAÇÃO CORS
    ============================ --}}
    <h3>🧩 Configuração CORS (config/cors.php)</h3>
    <pre class="bg-dark text-light p-3 rounded"><code>{{ json_encode($cors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>

    <hr>

    {{-- ===========================
         ARQUIVOS IMPORTANTES
    ============================ --}}
    <h3>📁 Arquivos Importantes</h3>

    @foreach($files as $name => $content)
        <details class="mb-3">
            <summary><strong>{{ $name }}</strong></summary>
            <pre class="bg-light border p-3 mt-2" style="max-height: 450px; overflow-y:auto;">
                <code>{{ $content }}</code>
            </pre>
        </details>
    @endforeach
</div>
@endsection
