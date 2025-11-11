@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Associações Escola Mãe ↔ Filhas</h2>

    {{-- Formulário para criar nova associação --}}
    <form method="POST" action="{{ route('master.escolas.associar') }}" class="row g-3 mb-4">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Escola Mãe (Secretaria)</label>
            <select name="mae_id" class="form-select" required>
                <option value="">-- escolha --</option>
                @foreach($escolasMae as $mae)
                    <option value="{{ $mae->id }}">{{ $mae->nome_e }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Escola Filha</label>
            <select name="filha_id" class="form-select" required>
                @foreach(\App\Models\Escola::whereNotNull('secretaria_id')->get() as $filha)
                    <option value="{{ $filha->id }}">{{ $filha->nome_e }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Associar</button>
        </div>
    </form>

    <h2>Ver Escolas Filhas</h2>
    @include('master.escolas._list_assoc', [
        'escolasMae' => $escolasMae,
        'maeSelecionada' => $maeSelecionada,
        'escolasFilhas' => $escolasFilhas,
        'nomeMae' => $nomeMae,
    ])
    
</div>
@endsection




{{-- Select para listar filhas de uma mãe -}}
    <form method="GET" action="{{ route('master.escolas.associacoes') }}" class="mb-3">
        <label for="mae_id">Ver Filhas de:</label>
        <select name="mae_id" id="mae_id" class="form-select d-inline w-auto">
            <option value="">-- escolha --</option>
            @foreach($escolasMae as $mae)
                <option value="{{ $mae->id }}" {{ $maeSelecionada == $mae->id ? 'selected' : '' }}>
                    {{ $mae->nome_e }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Ver</button>
    </form>

    {{-- Tabela de filhas -}}
    @if($maeSelecionada && $nomeMae)
        <h3>Escolas Filhas de <strong>{{ $nomeMae }}</strong></h3>
        @if($escolasFilhas->isNotEmpty())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>INEP</th>
                        <th>CNPJ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($escolasFilhas as $filha)
                        <tr>
                            <td>{{ $filha->id }}</td>
                            <td>{{ $filha->nome_e }}</td>
                            <td>{{ $filha->inep }}</td>
                            <td>{{ $filha->cnpj }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhuma escola filha vinculada.</p>
        @endif
    @endif
    --}}