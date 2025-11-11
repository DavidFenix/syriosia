<form method="GET" action="{{ isset($dashboard) ? route('master.dashboard') : route('master.escolas.associacoes') }}#idassoc" class="row g-3">
    <div class="col-md-6">
        <label for="mae_id" class="form-label">Selecione a Escola Mãe</label>
        <select name="mae_id" id="mae_id" class="form-select">
            <option value="">-- Escolha --</option>
            @foreach($escolasMae as $mae)
                <option value="{{ $mae->id }}" {{ ($maeSelecionada ?? '') == $mae->id ? 'selected' : '' }}>
                    {{ $mae->nome_e }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary w-100">Ver Filhas</button>
    </div>
</form>

@if(!empty($maeSelecionada) && !empty($nomeMae))
    <hr>
    <h4>Escolas Filhas de <strong>{{ $nomeMae }}</strong></h4>

    @if(!empty($escolasFilhas) && $escolasFilhas->isNotEmpty())
        <table class="table table-bordered mt-3">
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
        <p class="text-muted">Nenhuma escola filha vinculada a esta mãe.</p>
    @endif
@endif
    







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