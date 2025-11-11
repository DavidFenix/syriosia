@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">🏫 Associações: Escolas Mãe ↔ Filhas</h2>

    @php
        $auth = auth()->user();
    @endphp

    {{-- ⚙️ Formulário de associação --}}
    @if($auth && ($auth->hasRole('master') || $auth->is_super_master))
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('master.escolas.associar') }}" class="row g-3">
                    @csrf

                    {{-- ESCOLA MÃE --}}
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Escola Mãe (Secretaria)</label>
                        <select name="mae_id" class="form-select" required>
                            <option value="">-- escolha --</option>
                            @foreach($escolasMae as $mae)
                                <option value="{{ $mae->id }}">{{ $mae->nome_e }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Apenas secretarias e escolas-mãe podem receber filhas.</small>
                    </div>

                    {{-- ESCOLA FILHA --}}
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Escola Filha</label>
                        <select name="filha_id" class="form-select" required>
                            <option value="">-- escolha --</option>
                            @foreach($escolasFilhasDisponiveis as $filha)
                                @php
                                    $maeAtual = $filha->mae;
                                @endphp
                                <option value="{{ $filha->id }}">
                                    {{ $filha->nome_e }}
                                    @if($maeAtual)
                                        (já filha de {{ $maeAtual->nome_e }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Escolas que já são mães não aparecem aqui.</small>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">🔗 Associar</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            🚫 Apenas usuários <strong>Master</strong> ou <strong>Super Master</strong> podem criar associações entre escolas.
        </div>
    @endif


    {{-- ===========================
         TABELA DE ASSOCIAÇÕES
    ============================ --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">📋 Situação das Escolas</h4>

            <table id="associacoesTable" class="table table-bordered align-middle w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Secretaria (Mãe)</th>
                        <th>Filhas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Escola::orderBy('nome_e')->get() as $e)
                        @php
                            $temFilhas = $e->filhas()->count() > 0;
                            $mae = $e->mae;
                        @endphp
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>{{ $e->nome_e }}</td>
                            <td>
                                @if($e->is_master)
                                    <span class="badge bg-danger">Secretaria Master</span>
                                @elseif($temFilhas)
                                    <span class="badge bg-primary">MÃE</span>
                                @elseif($mae)
                                    <span class="badge bg-success">FILHA</span>
                                @else
                                    <span class="badge bg-secondary">ISOLADA</span>
                                @endif
                            </td>
                            <td>{{ $mae->nome_e ?? '—' }}</td>
                            <td>
                                @if($temFilhas)
                                    @php $total = $e->filhas->count(); @endphp
                                    <div class="filhas-container" id="filhas-{{ $e->id }}">
                                        <ul class="list-unstyled mb-1 d-none">
                                            @foreach($e->filhas as $f)
                                                <li>• {{ $f->nome_e }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info toggle-filhas"
                                                data-target="#filhas-{{ $e->id }}">
                                            👁️ Ver {{ $total }} {{ Str::plural('filha', $total) }}
                                        </button>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar nome"></th>
                        <th>
                            <select class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="Secretaria Master">Secretaria Master</option>
                                <option value="MÃE">Mãe</option>
                                <option value="FILHA">Filha</option>
                                <option value="ISOLADA">Isolada</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar secretaria"></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- JavaScript do botão "ver mais / ver menos" --}}
@push('scripts')
<script>
$(document).ready(function () {

    // 🎯 Reanexa o evento de clique compatível com DataTables
    $('#associacoesTable').on('click', '.toggle-filhas', function () {
        const target = $(this).data('target');
        const container = $(target).find('ul');
        const isHidden = container.hasClass('d-none');
        
        if (isHidden) {
            container.removeClass('d-none');
            $(this).text('🔽 Ocultar filhas');
        } else {
            container.addClass('d-none');
            const count = container.find('li').length;
            $(this).text(`👁️ Ver ${count} ${count === 1 ? 'filha' : 'filhas'}`);
        }
    });

});
</script>
@endpush


@endsection





{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>🏫 Associações Escola Mãe ↔ Filhas</h2>

    @php
        $auth = auth()->user();
    @endphp

    {{-- ⚙️ Formulário de associação: visível apenas para Master e Super Master -}}
    @if($auth && ($auth->hasRole('master') || $auth->is_super_master))
        <form method="POST" action="{{ route('master.escolas.associar') }}" class="row g-3 mb-4">
            @csrf

            {{-- ESCOLA MÃE -}}
            <div class="col-md-5">
                <label class="form-label fw-semibold">Escola Mãe (Secretaria)</label>
                <select name="mae_id" class="form-select" required>
                    <option value="">-- escolha --</option>
                    @foreach($escolasMae as $mae)
                        <option value="{{ $mae->id }}">{{ $mae->nome_e }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Apenas secretarias e escolas-mãe podem receber filhas.</small>
            </div>

            {{-- ESCOLA FILHA -}}
            <div class="col-md-5">
                <label class="form-label fw-semibold">Escola Filha</label>
                <select name="filha_id" class="form-select" required>
                    <option value="">-- escolha --</option>
                    @foreach($escolasFilhasDisponiveis as $filha)
                        @php
                            // Descobre se essa escola já é filha de alguém
                            $maeAtual = $filha->mae;
                        @endphp
                        <option value="{{ $filha->id }}">
                            {{ $filha->nome_e }}
                            @if($maeAtual)
                                (já filha de {{ $maeAtual->nome_e }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Escolas que já são mães não aparecem aqui.</small>
            </div>

            {{-- BOTÃO -}}
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">🔗 Associar</button>
            </div>
        </form>
    @else
        <div class="alert alert-warning">
            🚫 Apenas usuários <strong>Master</strong> ou <strong>Super Master</strong> podem criar associações entre escolas.
        </div>
    @endif


    {{-- =======================
         Tabela de Associações
    ======================== -}}
    <h3 class="mt-5 mb-3">📋 Situação das Escolas</h3>

    <table class="table table-bordered align-middle shadow-sm">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;">#</th>
                <th>Nome da Escola</th>
                <th>Tipo</th>
                <th>Secretaria (Mãe)</th>
                <th>Filhas (se houver)</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Escola::orderBy('nome_e')->get() as $e)
                @php
                    $temFilhas = $e->filhas()->count() > 0;
                    $mae = $e->mae;
                @endphp
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ $e->nome_e }}</td>
                    <td>
                        @if($e->is_master)
                            <span class="badge bg-danger">Secretaria Master</span>
                        @elseif($temFilhas)
                            <span class="badge bg-primary">MÃE</span>
                        @elseif($mae)
                            <span class="badge bg-success">FILHA</span>
                        @else
                            <span class="badge bg-secondary">ISOLADA</span>
                        @endif
                    </td>
                    <td>{{ $mae->nome_e ?? '—' }}</td>
                    <td>
                        @if($temFilhas)
                            <ul class="mb-0">
                                @foreach($e->filhas as $f)
                                    <li>{{ $f->nome_e }}</li>
                                @endforeach
                            </ul>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
--}}


{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>🏫 Associações Escola Mãe ↔ Filhas</h2>

    {{-- ⚙️ Obtém usuário autenticado -}}
    @php
        $auth = auth()->user();
    @endphp

    {{-- ⚙️ Formulário: só aparece para Masters e Super Masters -}}
    @if($auth && ($auth->hasRole('master') || $auth->is_super_master))
        <form method="POST" action="{{ route('master.escolas.associar') }}" class="row g-3 mb-4">
            @csrf

            <div class="col-md-5">
                <label class="form-label fw-semibold">Escola Mãe (Secretaria)</label>
                <select name="mae_id" class="form-select" required>
                    <option value="">-- escolha --</option>
                    @foreach($escolasMae as $mae)
                        <option value="{{ $mae->id }}">{{ $mae->nome_e }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Apenas secretarias e escolas-mãe podem receber filhas.</small>
            </div>

            <div class="col-md-5">
                <label class="form-label fw-semibold">Escola Filha</label>
                <select name="filha_id" class="form-select" required>
                    <option value="">-- escolha --</option>
                    @foreach($escolasFilhasDisponiveis as $filha)
                        <option value="{{ $filha->id }}">{{ $filha->nome_e }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Escolas que já são mães não aparecem nesta lista.</small>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">🔗 Associar</button>
            </div>
        </form>
    @else
        <div class="alert alert-warning">
            🚫 Apenas usuários <strong>Master</strong> ou <strong>Super Master</strong> podem criar associações entre escolas.
        </div>
    @endif


    {{-- =======================
         Tabela de Associações
    ======================== -}}
    <h3 class="mt-5 mb-3">📋 Situação das Escolas</h3>

    <table class="table table-bordered align-middle shadow-sm">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;">#</th>
                <th>Nome da Escola</th>
                <th>Tipo</th>
                <th>Secretaria (Mãe)</th>
                <th>Filhas (se houver)</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Escola::orderBy('nome_e')->get() as $e)
                @php
                    $temFilhas = $e->filhas()->count() > 0;
                    $mae = $e->mae;
                @endphp
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ $e->nome_e }}</td>
                    <td>
                        @if($e->is_master)
                            <span class="badge bg-danger">Secretaria Master</span>
                        @elseif($temFilhas)
                            <span class="badge bg-primary">MÃE</span>
                        @elseif($mae)
                            <span class="badge bg-success">FILHA</span>
                        @else
                            <span class="badge bg-secondary">ISOLADA</span>
                        @endif
                    </td>
                    <td>{{ $mae->nome_e ?? '—' }}</td>
                    <td>
                        @if($temFilhas)
                            <ul class="mb-0">
                                @foreach($e->filhas as $f)
                                    <li>{{ $f->nome_e }}</li>
                                @endforeach
                            </ul>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
--}}

{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Associações Escola Mãe ↔ Filhas</h2>

    @php
        $auth = auth()->user();
    @endphp

    {{-- ⚙️ Formulário: só aparece para Masters e Super Masters -}}
    @if($auth->hasRole('master') || $auth->is_super_master)
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
                    <option value="">-- escolha --</option>
                    @foreach(\App\Models\Escola::where('is_master', 0)->orderBy('nome_e')->get() as $filha)
                        <option value="{{ $filha->id }}">{{ $filha->nome_e }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Associar</button>
            </div>
        </form>
    @else
        <div class="alert alert-warning">
            🚫 Apenas usuários Master ou Super Master podem criar associações entre escolas.
        </div>
    @endif

    <h2>Ver Escolas Filhas</h2>

    @include('master.escolas._list_assoc', [
        'escolasMae' => $escolasMae,
        'maeSelecionada' => $maeSelecionada,
        'escolasFilhas' => $escolasFilhas,
        'nomeMae' => $nomeMae,
    ])
</div>
@endsection
--}}



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Associações Escola Mãe ↔ Filhas</h2>

    {{-- Formulário para criar nova associação -}}
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
--}}




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