@extends('layouts.app')
{{--passo 3: quatro informações chagaram aqui através de compact('escolasMae', 'maeSelecionada', 'escolasFilhas', 'nomeMae'))
==>serão acessadas como {{$escolasMae}} e {{$maeSelecionada}} e {{$escolasFilhas}} e {{$nomeMae}}
--}}
@section('content')
<div class="container">
    <h1>Associações Escola Mãe ↔ Filhas</h1>

    <!-- Select de Escolas Mãe -->
    <form method="GET" action="{{ route('master.escolas.associacoes2') }}" class="mb-3">
        <label for="mae_id">Selecione a Escola Mãe:</label>
        <select name="mae_id" id="mae_id" class="form-select d-inline w-auto">
            <option value="">-- escolha --</option>
            @foreach($escolasMae as $mae)
                <option value="{{ $mae->id }}" {{ $maeSelecionada == $mae->id ? 'selected' : '' }}>
                    {{ $mae->nome_e }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Ver Filhas</button>
    </form>

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
            <p>Nenhuma escola filha vinculada a esta mãe.</p>
        @endif
    @endif
</div>
@endsection
