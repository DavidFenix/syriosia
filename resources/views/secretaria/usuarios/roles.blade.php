@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Gerenciar Roles de {{ $usuario->nome_u }}</h1>

    {{-- ⚠️ Avisos de contexto --}}
    @if($isSelf)
        <div class="alert alert-warning">
            ⚙️ Você está gerenciando <strong>suas próprias roles</strong>. <br>
            Sua role <strong>secretaria</strong> é fixa e não pode ser removida. <br>
            Você pode adicionar ou remover outros papéis, como “professor”, em escolas da sua secretaria.
        </div>
    @elseif(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($schoolIdSelecionada)
        <div class="alert alert-info mb-4">
            <strong>🎯 Escola atual:</strong>
            {{ optional($escolas->firstWhere('id', $schoolIdSelecionada))->nome_e ?? 'Desconhecida' }} <br>
            <strong>Usuário:</strong> {{ $usuario->nome_u }} (ID: {{ $usuario->id }})
        </div>
    @endif

    {{-- 📊 Visão geral das roles --}}
    @if($usuario->roles->isNotEmpty())
        <div class="card mb-4 border border-primary">
            <div class="card-header bg-primary text-white">
                📋 Visão geral de roles deste usuário
            </div>
            <div class="card-body p-2">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Escola</th>
                            <th>Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuario->roles->groupBy('pivot.school_id') as $sid => $rolesGrupo)
                            @php
                                $nomeEscola = optional($escolas->firstWhere('id', $sid))->nome_e ?? '—';
                            @endphp
                            <tr>
                                <td>{{ $nomeEscola }}</td>
                                <td>
                                    @foreach($rolesGrupo as $r)
                                        <span class="badge bg-secondary me-1">
                                            {{ $r->role_name }}
                                            @if($isSelf && $r->role_name === 'secretaria')
                                                🔒
                                            @endif
                                        </span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 🏫 Seleção da escola --}}
    <form method="GET" action="{{ route('secretaria.usuarios.roles.edit', $usuario) }}" class="mb-4">
        <label class="form-label">Escolha a escola:</label>
        <div class="input-group">
            <select name="school_id" class="form-select" onchange="this.form.submit()">
                <option value="">Selecione...</option>
                @foreach($escolas as $e)
                    <option value="{{ $e->id }}" {{ $schoolIdSelecionada == $e->id ? 'selected' : '' }}>
                        {{ $e->nome_e }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- 🔧 Área de edição das roles --}}
    @if($schoolIdSelecionada)
        <form method="POST" action="{{ route('secretaria.usuarios.roles.update', $usuario) }}">
            @csrf
            <input type="hidden" name="school_id" value="{{ $schoolIdSelecionada }}">

            <div class="card card-body shadow-sm mb-4">
                <h5>🧩 Papéis disponíveis nesta escola</h5>

                @php
                    $rolesPermitidas = $roles->whereNotIn('role_name', ['master']); // secretaria tratada abaixo
                    $rolesSelecionadas = $usuario->roles()
                        ->wherePivot('school_id', $schoolIdSelecionada)
                        ->pluck('role_id')
                        ->toArray();

                    $isSecretarioAqui = $usuario->roles()
                        ->where('role_name', 'secretaria')
                        ->wherePivot('school_id', $secretaria->id)
                        ->exists();
                @endphp

                @foreach($rolesPermitidas as $role)
                    @php
                        $checked = in_array($role->id, $rolesSelecionadas);
                        $disabled = false;

                        // 🔒 Condições de bloqueio
                        if ($role->role_name === 'secretaria') {
                            // A role secretaria é sempre bloqueada, para todos
                            $disabled = true;
                        } elseif ($isSecretarioAqui && !$isSelf) {
                            // Outro secretário — bloqueado total
                            $disabled = true;
                        } elseif ($isSelf && $role->role_name === 'master') {
                            // Caso de segurança adicional: bloquear master sempre
                            $disabled = true;
                        }
                    @endphp

                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->id }}"
                            id="role_{{ $role->id }}"
                            {{ $checked ? 'checked' : '' }}
                            {{ $disabled ? 'disabled' : '' }}
                        >
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ ucfirst($role->role_name) }}
                            @if($role->role_name === 'secretaria')
                                🔒
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>

            {{-- Botões --}}
            <div class="d-flex justify-content-end">
                @if(!$isSecretarioAqui || $isSelf)
                    <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
                @else
                    <button type="button" class="btn btn-secondary" disabled>
                        🔒 Alteração bloqueada
                    </button>
                @endif
                <a href="{{ route('secretaria.usuarios.index') }}" class="btn btn-outline-secondary ms-2">
                    Voltar
                </a>
            </div>
        </form>
    @else
        <div class="alert alert-info">
            👈 Selecione uma escola acima para ver e editar as roles do usuário.
        </div>
    @endif

    <p class="text-muted mt-3 small">
        💡 As roles exibidas acima correspondem apenas à escola selecionada.  
        Para visualizar ou alterar roles em outra escola, selecione-a no menu superior.  
        O símbolo 🔒 indica roles fixas, que não podem ser alteradas nesta tela.
    </p>
</div>
@endsection



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Gerenciar Roles de {{ $usuario->nome_u }}</h1>

    {{-- 💡 Alerta de contexto -}}
    @if($schoolIdSelecionada)
        <div class="alert alert-info mb-4">
            <strong>🎯 Escola selecionada:</strong> 
            {{ optional($escolas->firstWhere('id', $schoolIdSelecionada))->nome_e ?? 'Desconhecida' }}
            <br>
            <strong>Usuário:</strong> {{ $usuario->nome_u }} (ID: {{ $usuario->id }})
        </div>
    @endif

    @if($isSelf)
        <div class="alert alert-warning">
            ⚙️ Você está editando <strong>suas próprias roles</strong>.  
            Sua role <strong>secretaria</strong> é fixa e não pode ser removida.
        </div>
    @endif


    {{-- 📊 Visão geral das roles -}}
    @if($usuario->roles->isNotEmpty())
        <div class="card mb-4 border border-primary">
            <div class="card-header bg-primary text-white">
                📋 Visão geral de roles deste usuário
            </div>
            <div class="card-body p-2">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Escola</th>
                            <th>Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuario->roles->groupBy('pivot.school_id') as $sid => $rolesGrupo)
                            @php
                                $nomeEscola = optional($escolas->firstWhere('id', $sid))->nome_e ?? '—';
                            @endphp
                            <tr>
                                <td>{{ $nomeEscola }}</td>
                                <td>
                                    @foreach($rolesGrupo as $r)
                                        <span class="badge bg-secondary me-1">{{ $r->role_name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 🏫 Seleção da escola -}}
    <form method="GET" action="{{ route('secretaria.usuarios.roles.edit', $usuario) }}" class="mb-4">
        <label class="form-label">Escolha a escola:</label>
        <div class="input-group">
            <select name="school_id" class="form-select" onchange="this.form.submit()">
                <option value="">Selecione...</option>
                @foreach($escolas as $e)
                    <option value="{{ $e->id }}" {{ $schoolIdSelecionada == $e->id ? 'selected' : '' }}>
                        {{ $e->nome_e }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- 🔧 Área de edição das roles -}}
    @if($schoolIdSelecionada)
        <form method="POST" action="{{ route('secretaria.usuarios.roles.update', $usuario) }}">
            @csrf
            <input type="hidden" name="school_id" value="{{ $schoolIdSelecionada }}">

            <div class="card card-body shadow-sm mb-4">
                <h5>🧩 Papéis disponíveis nesta escola</h5>

                @php
                    $rolesPermitidas = $roles->whereNotIn('role_name', ['master', 'secretaria']);
                    $rolesSelecionadas = $usuario->roles()
                        ->wherePivot('school_id', $schoolIdSelecionada)
                        ->pluck('role_id')
                        ->toArray();

                    $auth = auth()->user();
                    $isSelf = $auth->id === $usuario->id;

                    // detecta se o usuário alvo é secretário nesta secretaria
                    $isSecretarioAqui = $usuario->roles()
                        ->where('role_name', 'secretaria')
                        ->wherePivot('school_id', $secretaria->id)
                        ->exists();
                @endphp

                @foreach($rolesPermitidas as $role)
                    @php
                        $checked = in_array($role->id, $rolesSelecionadas);
                        $disabled = false;

                        // Bloqueia a edição em cenários sensíveis
                        if ($isSelf) {
                            $disabled = true;
                        } elseif ($isSecretarioAqui) {
                            $disabled = true;
                        }
                    @endphp

                    <div class="form-check">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->id }}"
                            id="role_{{ $role->id }}"
                            class="form-check-input"
                            {{ $checked ? 'checked' : '' }}
                            {{ $disabled ? 'disabled' : '' }}
                        >
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ ucfirst($role->role_name) }}
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end">
                @if(!$isSelf && !$isSecretarioAqui)
                    <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
                @else
                    <button type="button" class="btn btn-secondary" disabled>
                        🔒 Alteração bloqueada
                    </button>
                @endif
                <a href="{{ route('secretaria.usuarios.index') }}" class="btn btn-outline-secondary ms-2">
                    Voltar
                </a>
            </div>
        </form>
    @else
        <div class="alert alert-info">
            👈 Selecione uma escola acima para ver e editar as roles do usuário.
        </div>
    @endif

    <p class="text-muted mt-3 small">
        💡 As roles exibidas acima correspondem apenas à escola selecionada.  
        Para visualizar ou alterar roles em outra escola, selecione-a no menu superior.  
        Use o painel “Visão geral” para conferir todos os vínculos.
    </p>
</div>
@endsection
--}}