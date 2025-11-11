@extends('layouts.app')


@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Gerenciar Roles de {{ $usuario->nome_u }}</h1>

    @if($schoolIdSelecionada)
        <div class="alert alert-info mb-4">
            <strong>🎯 Escola atual:</strong> 
            {{ optional($escolas->firstWhere('id', $schoolIdSelecionada))->nome_e ?? 'Desconhecida' }}
            <br>
            <strong>Usuário:</strong> {{ $usuario->nome_u }} (ID: {{ $usuario->id }})
        </div>
    @endif


    @if($usuario->roles->isNotEmpty())
        <div class="card mb-4 border border-primary">
            <div class="card-header bg-primary text-white">
                📊 Visão geral de roles deste usuário
            </div>
            <div class="card-body p-2">
                <table class="table table-sm mb-0">
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


    {{-- 🔹 Seleção da escola --}}
    <form method="GET" action="{{ route('master.usuarios.roles.edit', $usuario) }}" class="mb-4">
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

    <pre>
{{--Escola atual: {{ $schoolIdSelecionada }}
Usuario atual: {{$usuario->id}}
Roles do usuário nesta escola:
{{ json_encode($rolesSelecionadas) --}}
</pre>


    {{-- 🔹 Exibição das roles --}}
    @if($schoolIdSelecionada)
        <form method="POST" action="{{ route('master.usuarios.roles.update', $usuario) }}">
            @csrf
            <input type="hidden" name="school_id" value="{{ $schoolIdSelecionada }}">

            <div class="card card-body shadow-sm mb-4">
                <h5>🧩 Papéis disponíveis para esta escola</h5>

                @php
                    $auth = auth()->user();
                    $isSuperAuth = $auth->is_super_master;
                    $isMasterAuth = $auth->hasRole('master') && !$auth->is_super_master;
                    $isSuperUsuario = $usuario->is_super_master;
                    $isMasterUsuario = $usuario->roles->pluck('role_name')->contains('master');
                @endphp

                @foreach($roles as $role)
                    @php
                        $isMasterRole = $role->role_name === 'master';
                        $checked = in_array($role->id, $rolesSelecionadas);

                        // 🔒 Condições de bloqueio visual
                        $desabilitar = false;

                        // 1️⃣ Super Master alvo -> não pode remover master
                        if ($isSuperUsuario && $isMasterRole) {
                            $desabilitar = true;
                        }

                        // 2️⃣ Master comum autenticado alterando outro Master ou Super Master
                        if ($isMasterAuth && ($isSuperUsuario || $isMasterUsuario) && $auth->id !== $usuario->id) {
                            $desabilitar = true;
                        }

                        // 3️⃣ Master comum alterando a si mesmo -> não pode remover sua role master
                        if ($isMasterAuth && $auth->id === $usuario->id && $isMasterRole) {
                            $desabilitar = true;
                        }

                        // 4️⃣ Usuário comum autenticado -> nunca pode alterar nada
                        if (!$isSuperAuth && !$isMasterAuth) {
                            $desabilitar = true;
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
                            {{ $desabilitar ? 'disabled' : '' }}
                        >
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ ucfirst($role->role_name) }}
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
                <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary ms-2">Voltar</a>
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
        Use o painel “Visão geral de roles” para ver todas as associações.
    </p>

</div>
@endsection









{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Gerenciar Roles de {{ $usuario->nome_u }}</h1>

    {{-- Escolher escola -}}
    <form method="GET" action="{{ route('master.usuarios.roles.edit', $usuario) }}" class="mb-4">
        <label class="form-label">Escolha a escola:</label>
        <div class="input-group">
            @php
                //regra:preparando a variavel para não deixar excluir role master
                $esc_is_master = null; // inicializa antes do loop
            @endphp

            <select name="school_id" class="form-select" onchange="this.form.submit()">
                <option value="">Selecione...</option>

                @foreach($escolas as $e)
                    @if($schoolIdSelecionada == $e->id && $e->is_master)
                        @php
                            $esc_is_master = $e->is_master;
                        @endphp
                    @endif

                    <option value="{{ $e->id }}" {{ $schoolIdSelecionada == $e->id ? 'selected' : '' }}>
                        {{ $e->nome_e }}
                    </option>
                @endforeach
            </select>

        </div>
    </form>

    {{-- Exibir roles apenas se escola selecionada -}}
    @if($schoolIdSelecionada)
        <form method="POST" action="{{ route('master.usuarios.roles.update', $usuario) }}">
            @csrf
            <input type="hidden" name="school_id" value="{{ $schoolIdSelecionada }}">

            <div class="card card-body shadow-sm mb-4">
                <h5>🧩 Papéis disponíveis para esta escola</h5>

                @foreach($roles as $role)
                    @if($esc_is_master && $role->role_name === 'master')
                        {{--regra:não deixa desmarcar role master do usuario master da secretaria master-}}
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="roles[]"
                                   value="{{ $role->id }}"
                                   id="role_{{ $role->id }}"
                                   {{ in_array($role->id, $rolesSelecionadas) ? 'checked' : '' }}
                                   disabled>
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ ucfirst($role->role_name) }}
                            </label>
                        </div>
                    @else
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="roles[]"
                                   value="{{ $role->id }}"
                                   id="role_{{ $role->id }}"
                                   {{ in_array($role->id, $rolesSelecionadas) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ ucfirst($role->role_name) }}
                            </label>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
                <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary ms-2">Voltar</a>
            </div>
        </form>
    @else
        <div class="alert alert-info">
            👈 Selecione uma escola acima para ver e editar as roles do usuário.
        </div>
    @endif

</div>
@endsection
--}}