@extends('layouts.app')

@section('title', 'Detalhes da Escola')

@section('content')
<div class="container">
    <h2 class="mb-4">🏫 Detalhes da Escola</h2>

    @php
        // ==========================
        // 📊 Estatísticas gerais
        // ==========================
        $usuarios = $escola->usuarios;
        $totalUsuarios = $usuarios->count();
        $totalFilhas = $escola->filhas->count();

        $todasRoles = collect();
        foreach ($usuarios as $u) {
            foreach ($u->roles as $r) {
                if ($r->pivot->school_id == $escola->id) {
                    $todasRoles->push($r->role_name);
                }
            }
        }

        $contagemRoles = $todasRoles->countBy();

        $tipo = $escola->is_master
            ? 'Secretaria Master'
            : ($escola->filhas->count() > 0
                ? 'Escola Mãe'
                : ($escola->mae ? 'Escola Filha' : 'Escola Isolada'));
    @endphp

    {{-- ==========================
         📈 RESUMO ESTATÍSTICO
       ========================== --}}
    <div class="alert alert-info shadow-sm mb-4">
        <h5 class="fw-bold mb-3">📊 Resumo da Instituição</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="mb-0">
                    <li><strong>Tipo:</strong> {{ $tipo }}</li>
                    <li><strong>Total de usuários:</strong> {{ $totalUsuarios }}</li>
                    @if($totalFilhas > 0)
                        <li><strong>Escolas filhas:</strong> {{ $totalFilhas }}</li>
                    @endif
                </ul>
            </div>
            <div class="col-md-6">
                @if($contagemRoles->count())
                    <ul class="mb-0">
                        @foreach($contagemRoles as $nome => $qtde)
                            <li><strong>{{ ucfirst($nome) }}{{ $qtde > 1 ? 's' : '' }}:</strong> {{ $qtde }}</li>
                        @endforeach
                    </ul>
                @else
                    <em>Nenhum papel atribuído ainda.</em>
                @endif
            </div>
        </div>
    </div>

    {{-- ==========================
         🧭 ABAS DE CONTEÚDO
       ========================== --}}
    <ul class="nav nav-tabs mb-3" id="schoolTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados"
                type="button" role="tab" aria-controls="dados" aria-selected="true">
                🏫 Dados Gerais
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="hierarquia-tab" data-bs-toggle="tab" data-bs-target="#hierarquia"
                type="button" role="tab" aria-controls="hierarquia" aria-selected="false">
                🧩 Hierarquia
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios"
                type="button" role="tab" aria-controls="usuarios" aria-selected="false">
                👥 Usuários
            </button>
        </li>
    </ul>

    <div class="tab-content" id="schoolTabsContent">
        {{-- ==========================
             🏫 Aba 1: DADOS GERAIS
           ========================== --}}
        <div class="tab-pane fade show active" id="dados" role="tabpanel" aria-labelledby="dados-tab">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-2">{{ $escola->nome_e }}</h4>
                    <p class="text-muted mb-1">Tipo: <strong>{{ $tipo }}</strong></p>
                    @if($escola->mae)
                        <p class="text-muted mb-1">Secretaria/Mãe:
                            <strong>{{ $escola->mae->nome_e }}</strong>
                        </p>
                    @endif
                    <p class="text-muted mb-1">Cidade: <strong>{{ $escola->cidade ?? '—' }}</strong></p>
                    <p class="text-muted mb-1">Estado: <strong>{{ $escola->estado ?? '—' }}</strong></p>
                    <p class="text-muted mb-1">CNPJ: <strong>{{ $escola->cnpj ?? '—' }}</strong></p>
                    <p class="text-muted mb-1">Telefone: <strong>{{ $escola->telefone ?? '—' }}</strong></p>
                    <p class="text-muted mb-0">INEP: <strong>{{ $escola->inep ?? '—' }}</strong></p>
                </div>
            </div>
        </div>

        {{-- ==========================
             🧩 Aba 2: HIERARQUIA
           ========================== --}}
        <div class="tab-pane fade" id="hierarquia" role="tabpanel" aria-labelledby="hierarquia-tab">
            @if($escola->is_master || $escola->filhas->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold">
                        🧩 Escolas Filhas
                    </div>
                    <div class="card-body">
                        @if($escola->filhas->count())
                            <ul class="mb-0">
                                @foreach($escola->filhas as $filha)
                                    <li>
                                        {{ $filha->nome_e }}
                                        <small class="text-muted">
                                            ({{ $filha->cidade ?? '—' }}, ID: {{ $filha->id }})
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">Esta escola não possui filhas.</p>
                        @endif
                    </div>
                </div>
            @elseif($escola->mae)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold">
                        🏛️ Secretaria Responsável
                    </div>
                    <div class="card-body">
                        <p>{{ $escola->mae->nome_e }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ==========================
             👥 Aba 3: USUÁRIOS
           ========================== --}}
        <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">
                    👥 Usuários Vinculados a {{ $escola->nome_e }}
                </div>
                <div class="card-body">
                    @if($usuarios->count())
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Status</th>
                                    <th>Funções / Escolas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $u)
                                    <tr>
                                        <td>{{ $u->id }}</td>
                                        <td>{{ $u->nome_u }}</td>
                                        <td>{{ $u->cpf }}</td>
                                        <td>
                                            @if($u->status)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-secondary">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $rolesPorEscola = $u->roles->groupBy('pivot.school_id');
                                            @endphp
                                            @foreach($rolesPorEscola as $schoolId => $rolesGrupo)
                                                @php
                                                    $nomeEscolaRole = \App\Models\Escola::find($schoolId)?->nome_e ?? '—';
                                                @endphp
                                                <div class="mb-1">
                                                    <strong>{{ $nomeEscolaRole }}:</strong>
                                                    @foreach($rolesGrupo as $r)
                                                        <span class="badge bg-info text-dark">
                                                            {{ ucfirst($r->role_name) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">Nenhum usuário vinculado a esta escola.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 🔙 BOTÃO VOLTAR --}}
    <div class="mt-4">
        <a href="{{ route('master.escolas.index') }}" class="btn btn-secondary">
            ← Voltar à lista de escolas
        </a>
    </div>
</div>
@endsection




{{--
@extends('layouts.app')

@section('title', 'Detalhes da Escola')

@section('content')
<div class="container">
    <h2 class="mb-4">🏫 Detalhes da Escola</h2>

    @php
        // ==========================
        // 📊 Estatísticas gerais
        // ==========================
        $usuarios = $escola->usuarios;
        $totalUsuarios = $usuarios->count();
        $totalFilhas = $escola->filhas->count();

        // Junta todas as roles dessa escola
        $todasRoles = collect();
        foreach ($usuarios as $u) {
            foreach ($u->roles as $r) {
                // Conta apenas roles atribuídas a esta escola
                if ($r->pivot->school_id == $escola->id) {
                    $todasRoles->push($r->role_name);
                }
            }
        }

        // Contagem agrupada das roles
        $contagemRoles = $todasRoles->countBy();

        // Tipo textual para exibir
        $tipo = $escola->is_master
            ? 'Secretaria Master'
            : ($escola->filhas->count() > 0
                ? 'Escola Mãe'
                : ($escola->mae ? 'Escola Filha' : 'Escola Isolada'));
    @endphp

    {{-- ==========================
         📈 RESUMO ESTATÍSTICO
       ========================== -}}
    <div class="alert alert-info shadow-sm">
        <h5 class="fw-bold mb-3">📊 Resumo da Instituição</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="mb-0">
                    <li><strong>Tipo:</strong> {{ $tipo }}</li>
                    <li><strong>Total de usuários:</strong> {{ $totalUsuarios }}</li>
                    @if($totalFilhas > 0)
                        <li><strong>Escolas filhas:</strong> {{ $totalFilhas }}</li>
                    @endif
                </ul>
            </div>
            <div class="col-md-6">
                @if($contagemRoles->count())
                    <ul class="mb-0">
                        @foreach($contagemRoles as $nome => $qtde)
                            <li><strong>{{ ucfirst($nome) }}{{ $qtde > 1 ? 's' : '' }}:</strong> {{ $qtde }}</li>
                        @endforeach
                    </ul>
                @else
                    <em>Nenhum papel atribuído ainda.</em>
                @endif
            </div>
        </div>
    </div>

    {{-- ==========================
         🏫 DADOS PRINCIPAIS
       ========================== -}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title mb-2">{{ $escola->nome_e }}</h4>
            <p class="text-muted mb-1">Tipo: <strong>{{ $tipo }}</strong></p>
            @if($escola->mae)
                <p class="text-muted mb-1">Secretaria/Mãe:
                    <strong>{{ $escola->mae->nome_e }}</strong>
                </p>
            @endif
            <p class="text-muted mb-1">Cidade: <strong>{{ $escola->cidade ?? '—' }}</strong></p>
            <p class="text-muted mb-1">Estado: <strong>{{ $escola->estado ?? '—' }}</strong></p>
            <p class="text-muted mb-1">CNPJ: <strong>{{ $escola->cnpj ?? '—' }}</strong></p>
            <p class="text-muted mb-1">Telefone: <strong>{{ $escola->telefone ?? '—' }}</strong></p>
            <p class="text-muted mb-0">INEP: <strong>{{ $escola->inep ?? '—' }}</strong></p>
        </div>
    </div>

    {{-- ==========================
         🧩 HIERARQUIA
       ========================== -}}
    @if($escola->is_master || $escola->filhas->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">
                🧩 Escolas Filhas
            </div>
            <div class="card-body">
                @if($escola->filhas->count())
                    <ul class="mb-0">
                        @foreach($escola->filhas as $filha)
                            <li>
                                {{ $filha->nome_e }} 
                                <small class="text-muted">
                                    ({{ $filha->cidade ?? '—' }}, ID: {{ $filha->id }})
                                </small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">Esta escola não possui filhas.</p>
                @endif
            </div>
        </div>
    @elseif($escola->mae)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">
                🏛️ Secretaria Responsável
            </div>
            <div class="card-body">
                <p>{{ $escola->mae->nome_e }}</p>
            </div>
        </div>
    @endif

    {{-- ==========================
         👥 USUÁRIOS VINCULADOS
       ========================== -}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">
            👥 Usuários Vinculados a {{ $escola->nome_e }}
        </div>
        <div class="card-body">
            @if($usuarios->count())
                <table class="table table-sm table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Status</th>
                            <th>Funções / Escolas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->nome_u }}</td>
                                <td>{{ $u->cpf }}</td>
                                <td>
                                    @if($u->status)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $rolesPorEscola = $u->roles->groupBy('pivot.school_id');
                                    @endphp
                                    @foreach($rolesPorEscola as $schoolId => $rolesGrupo)
                                        @php
                                            $nomeEscolaRole = \App\Models\Escola::find($schoolId)?->nome_e ?? '—';
                                        @endphp
                                        <div class="mb-1">
                                            <strong>{{ $nomeEscolaRole }}:</strong>
                                            @foreach($rolesGrupo as $r)
                                                <span class="badge bg-info text-dark">
                                                    {{ ucfirst($r->role_name) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">Nenhum usuário vinculado a esta escola.</p>
            @endif
        </div>
    </div>

    {{-- ==========================
         🔙 BOTÃO VOLTAR
       ========================== -}}
    <div class="mt-4">
        <a href="{{ route('master.escolas.index') }}" class="btn btn-secondary">
            ← Voltar à lista de escolas
        </a>
    </div>
</div>
@endsection
--}}



{{--
@extends('layouts.app')

@section('title', 'Detalhes da Escola')

@section('content')
<div class="container">
    <h2 class="mb-4">🏫 Detalhes da Escola</h2>

    {{-- Cabeçalho -}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title mb-2">{{ $escola->nome_e }}</h4>
            <p class="text-muted mb-1">Tipo: <strong>{{ $tipo }}</strong></p>
            @if($escola->mae)
                <p class="text-muted mb-1">Secretaria/Mãe: 
                    <strong>{{ $escola->mae->nome_e }}</strong>
                </p>
            @endif
            <p class="text-muted mb-1">Cidade: <strong>{{ $escola->cidade ?? '—' }}</strong></p>
            <p class="text-muted mb-1">Estado: <strong>{{ $escola->estado ?? '—' }}</strong></p>
            <p class="text-muted mb-1">CNPJ: <strong>{{ $escola->cnpj ?? '—' }}</strong></p>
            <p class="text-muted mb-1">Telefone: <strong>{{ $escola->telefone ?? '—' }}</strong></p>
            <p class="text-muted mb-0">INEP: <strong>{{ $escola->inep ?? '—' }}</strong></p>
        </div>
    </div>

    {{-- Hierarquia -}}
    @if($escola->is_master || $escola->filhas->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">
                🧩 Escolas Filhas
            </div>
            <div class="card-body">
                @if($escola->filhas->count())
                    <ul>
                        @foreach($escola->filhas as $filha)
                            <li>{{ $filha->nome_e }} ({{ $filha->cidade ?? '—' }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">Esta escola não possui filhas.</p>
                @endif
            </div>
        </div>
    @elseif($escola->mae)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">
                🏛️ Secretaria Responsável
            </div>
            <div class="card-body">
                <p>{{ $escola->mae->nome_e }}</p>
            </div>
        </div>
    @endif

    {{-- Usuários vinculados -}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">
            👥 Usuários Vinculados
        </div>
        <div class="card-body">
            @if($escola->usuarios->count())
                <table class="table table-sm table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Status</th>
                            <th>Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($escola->usuarios as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->nome_u }}</td>
                                <td>{{ $u->cpf }}</td>
                                <td>
                                    @if($u->status)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($u->roles as $r)
                                        <span class="badge bg-info text-dark">
                                            {{ ucfirst($r->role_name) }}
                                        </span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">Nenhum usuário vinculado a esta escola.</p>
            @endif
        </div>
    </div>

    {{-- Voltar -}}
    <div class="mt-4">
        <a href="{{ route('master.escolas.index') }}" class="btn btn-secondary">
            ← Voltar à lista de escolas
        </a>
    </div>
</div>
@endsection
--}}