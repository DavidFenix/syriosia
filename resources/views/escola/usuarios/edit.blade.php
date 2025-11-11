@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuário</h1>

    {{-- 🔹 Cabeçalho informativo sobre o contexto --}}
    <div class="alert {{ $contexto['is_protegido'] ? 'alert-secondary' : 'alert-info' }}">
        <strong>🧾 Situação:</strong>
        @if($contexto['is_self'])
            <span>Você está editando sua própria conta.</span>
        @elseif($contexto['is_nativo'])
            <span>Usuário criado por esta escola.</span>
        @elseif($contexto['is_vinculado'])
            <span>Usuário apenas vinculado à sua escola.</span>
        @elseif($contexto['is_protegido'])
            <span>Usuário protegido (master/secretaria ou gestor da mesma escola).</span>
        @else
            <span>Usuário externo — não pertence à sua escola.</span>
        @endif
    </div>

    {{-- 🔒 Motivos de bloqueio --}}
    @if(!empty($contexto['motivos']))
        <div class="alert alert-warning">
            <ul class="mb-0">
                @foreach($contexto['motivos'] as $motivo)
                    <li>{{ $motivo }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 🚫 Bloqueio total --}}
    @if($flags['view_only'])
        <div class="alert alert-secondary">
            ⚠️ Este usuário não pode ser alterado neste contexto.
        </div>
    @endif

    @php
        // compatibilidade temporária com versões antigas
        $somenteLeituraTerceiros = $flags['view_only'] ?? false;
    @endphp


    <form method="POST" action="{{ route('escola.usuarios.update', $usuario) }}">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control"
                   value="{{ old('nome', $usuario->nome_u) }}"
                   {{ $flags['can_edit_nome'] ? '' : 'readonly' }}>
        </div>

        {{-- CPF --}}
        <div class="mb-3">
            <label class="form-label">CPF</label>
            <input type="text" class="form-control" value="{{ $usuario->cpf }}" readonly>
        </div>

        {{-- Status --}}
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" {{ $flags['can_edit_status'] ? '' : 'disabled' }}>
                <option value="1" {{ $usuario->status ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ !$usuario->status ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>

        {{-- Senha (somente se permitido) --}}
        @if($flags['can_edit_password'])
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="password" class="form-control" minlength="6"
                       placeholder="Deixe em branco se não quiser alterar">
                <input type="password" name="password_confirmation" class="form-control mt-2" minlength="6"
                       placeholder="Confirme a nova senha">
            </div>
        @endif

        {{-- Papéis (roles) agrupados por escola --}}
        <div class="mb-4">
            <label class="form-label">Papéis (roles) por escola</label>

            @php
                use App\Models\Escola;

                // Agrupa as roles por school_id via pivot
                $rolesPorEscola = $usuario->roles->groupBy(fn($r) => $r->pivot->school_id);
            @endphp

            @forelse($rolesPorEscola as $schoolId => $rolesGrupo)
                @php $escola = Escola::find($schoolId); @endphp

                <div class="border rounded p-3 mb-3 bg-light">
                    <strong class="d-block mb-2">
                        🏫 {{ $escola->nome_e ?? 'Escola desconhecida (ID '.$schoolId.')' }}
                    </strong>

                    <div class="ms-2">
                        @foreach($rolesGrupo as $role)
                            @php
                                $color = match($role->role_name) {
                                    'master' => 'danger',
                                    'secretaria' => 'primary',
                                    'escola' => 'info',
                                    'professor' => 'success',
                                    'aluno' => 'secondary',
                                    default => 'dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }} me-1">
                                {{ ucfirst($role->role_name) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhum papel atribuído a este usuário.</p>
            @endforelse
        </div>

        @if(Route::has('escola.usuarios.roles.edit') && !$flags['view_only'])
            <a href="{{ route('escola.usuarios.roles.edit', $usuario->id) }}"
               class="btn btn-outline-primary btn-sm mt-2">
                ⚙️ Gerenciar roles
            </a>
        @endif


        {{-- Botões --}}
        <div class="mt-4">
            @if(!$flags['view_only'])
                <button type="submit" class="btn btn-success">💾 Salvar alterações</button>
            @endif
            <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>
@endsection




{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuário</h1>

    @php
        use App\Models\Escola;

        $auth = auth()->user();
        $schoolId = session('current_school_id');
        $roles = $usuario->roles->pluck('role_name')->toArray();

        $isNativo = $usuario->school_id == $schoolId;
        $isSelf   = $usuario->id === $auth->id;

        $temRoleEscolaAuth = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $temRoleEscolaAlvo = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $isVinculado = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->exists() && !$isNativo;

        $isSuperior = in_array('master', $roles) || in_array('secretaria', $roles);

        // 🔒 bloqueios
        $somenteLeituraTerceiros =
            (!$isNativo && !$isSelf) ||
            $isSuperior ||
            ($temRoleEscolaAuth && $temRoleEscolaAlvo && !$isSelf);

        // SELF -> só altera senha
        $readOnlyCampos = $isSelf || $somenteLeituraTerceiros;
        $podeAlterarSenha = $isSelf;
        $podeGerenciarRoles = !$isSuperior && ($isNativo || $isSelf);
    @endphp

    {{-- 🔹 Cabeçalho informativo -}}
    <div class="alert {{ $somenteLeituraTerceiros ? 'alert-secondary' : 'alert-info' }}">
        <strong>🧾 Tipo de vínculo:</strong>
        @if($isSelf)
            <span>Você está editando sua própria conta.</span>
        @elseif($isNativo)
            <span>Usuário criado por esta escola.</span>
        @elseif($isVinculado)
            <span>Usuário apenas vinculado à sua escola.</span>
        @elseif($isSuperior)
            <span>Usuário de nível superior (Secretaria ou Master).</span>
        @else
            <span>Usuário externo — não pertence à sua escola.</span>
        @endif
    </div>

    {{-- 🚫 Bloqueio total -}}
    @if(!$isNativo && !$isSelf && !$isVinculado)
        <div class="alert alert-danger">
            🚫 Você não tem permissão para editar este usuário.
        </div>
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        @php return; @endphp
    @endif

    <form method="POST" action="{{ route('escola.usuarios.update', $usuario) }}">
        @csrf
        @method('PUT')

        {{-- Nome -}}
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome_u" class="form-control"
                   value="{{ old('nome_u', $usuario->nome_u) }}"
                   {{ $readOnlyCampos ? 'readonly' : '' }}>
        </div>

        {{-- CPF -}}
        <div class="mb-3">
            <label class="form-label">CPF</label>
            <input type="text" class="form-control" value="{{ $usuario->cpf }}" readonly>
        </div>

        {{-- Status -}}
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" {{ $readOnlyCampos ? 'disabled' : '' }}>
                <option value="1" {{ $usuario->status ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ !$usuario->status ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>

        {{-- Senha (somente self) -}}
        @if($podeAlterarSenha)
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="senha" class="form-control" minlength="6"
                       placeholder="Deixe em branco se não quiser alterar">
            </div>
        @endif

        {{-- Roles agrupadas por escola -}}
        <div class="mb-4">
            <label class="form-label">Papéis (roles) por escola</label>

            @forelse($usuario->roles->groupBy('pivot.school_id') as $sid => $rolesGrupo)
                @php $escola = Escola::find($sid); @endphp
                <div class="border rounded p-2 mb-2 bg-light">
                    <strong>{{ $escola->nome_e ?? 'Escola desconhecida' }}</strong>
                    <div class="mt-2">
                        @foreach($rolesGrupo as $r)
                            @php
                                $color = match($r->role_name) {
                                    'master' => 'danger',
                                    'secretaria' => 'primary',
                                    'escola' => 'info',
                                    'professor' => 'success',
                                    'aluno' => 'secondary',
                                    default => 'dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($r->role_name) }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhum papel atribuído.</p>
            @endforelse

            {{-- Botão Gerenciar Roles -}}
            @if($podeGerenciarRoles && Route::has('escola.usuarios.roles.edit'))
                <a href="{{ route('escola.usuarios.roles.edit', $usuario->id) }}"
                   class="btn btn-outline-primary btn-sm mt-2">
                    ⚙️ Gerenciar roles
                </a>
            @endif
        </div>

        {{-- Botões -}}
        <div class="mt-4">
            @if($podeAlterarSenha || (!$somenteLeituraTerceiros && !$isSelf))
                <button type="submit" class="btn btn-success">💾 Salvar alterações</button>
            @endif
            <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>
@endsection
--}}

{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuário</h1>

    @php
        use App\Models\Escola;

        $auth = auth()->user();
        $schoolId = session('current_school_id');
        $roles = $usuario->roles->pluck('role_name')->toArray();

        $isNativo = $usuario->school_id == $schoolId;
        $isSelf = $usuario->id === $auth->id;

        $temRoleEscolaAuth = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $temRoleEscolaAlvo = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $isVinculado = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->exists() && !$isNativo;

        $isSuperior = in_array('master', $roles) || in_array('secretaria', $roles);

        // 🔒 Hierarquia de bloqueio base
        $somenteLeitura =
            (!$isNativo && !$isSelf) ||          // externos
            $isSuperior ||                       // master/secretaria
            ($temRoleEscolaAuth && $temRoleEscolaAlvo && !$isSelf); // colega gestor

        // 💡 Permissões especiais
        $podeAlterarSenha = $isSelf;
        $podeGerenciarRoles = $isSelf || ($isNativo && !$isSuperior && !$temRoleEscolaAlvo);

        // 🔓 Exceção: o próprio usuário nunca deve ser bloqueado totalmente
        if ($isSelf) {
            $somenteLeitura = false;
        }
    @endphp

    {{-- 🔹 Cabeçalho informativo -}}
    <div class="alert {{ $somenteLeitura ? 'alert-secondary' : 'alert-info' }}">
        <strong>🧾 Tipo de vínculo:</strong>
        @if($isSelf)
            <span>Você está editando sua própria conta.</span>
        @elseif($isNativo)
            <span>Usuário criado por esta escola.</span>
        @elseif($isVinculado)
            <span>Usuário apenas vinculado à sua escola.</span>
        @elseif($isSuperior)
            <span>Usuário de nível superior (Secretaria ou Master).</span>
        @else
            <span>Usuário externo — não pertence à sua escola.</span>
        @endif
    </div>

    {{-- 🚫 Bloqueio total -}}
    @if(!$isNativo && !$isSelf && !$isVinculado)
        <div class="alert alert-danger">
            🚫 Você não tem permissão para editar este usuário.
        </div>
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        @php return; @endphp
    @endif

    <form method="POST" action="{{ route('escola.usuarios.update', $usuario) }}">
        @csrf
        @method('PUT')

        {{-- Nome -}}
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome_u" class="form-control"
                   value="{{ old('nome_u', $usuario->nome_u) }}"
                   {{ $somenteLeitura ? 'readonly' : '' }}>
        </div>

        {{-- CPF -}}
        <div class="mb-3">
            <label class="form-label">CPF</label>
            <input type="text" class="form-control" value="{{ $usuario->cpf }}" readonly>
        </div>

        {{-- Senha (somente self) -}}
        @if($podeAlterarSenha)
            <div class="alert alert-info small py-2">
                🔐 Você pode alterar sua senha aqui. Deixe em branco se não quiser mudar.
            </div>
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="senha" class="form-control" minlength="6"
                       placeholder="Digite uma nova senha">
            </div>
        @endif

        {{-- Status -}}
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" {{ $somenteLeitura ? 'disabled' : '' }}>
                <option value="1" {{ $usuario->status ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ !$usuario->status ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>

        {{-- Roles agrupadas -}}
        <div class="mb-4">
            <label class="form-label">Papéis (roles) por escola</label>

            @forelse($usuario->roles->groupBy('pivot.school_id') as $sid => $rolesGrupo)
                @php $escola = Escola::find($sid); @endphp
                <div class="border rounded p-2 mb-2 bg-light">
                    <strong>{{ $escola->nome_e ?? 'Escola desconhecida' }}</strong>
                    <div class="mt-2">
                        @foreach($rolesGrupo as $r)
                            @php
                                $color = match($r->role_name) {
                                    'master' => 'danger',
                                    'secretaria' => 'primary',
                                    'escola' => 'info',
                                    'professor' => 'success',
                                    'aluno' => 'secondary',
                                    default => 'dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($r->role_name) }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhum papel atribuído.</p>
            @endforelse

            {{-- Botão "Gerenciar roles" -}}
            @if($podeGerenciarRoles && Route::has('escola.usuarios.roles.edit'))
                <a href="{{ route('escola.usuarios.roles.edit', $usuario->id) }}"
                   class="btn btn-outline-primary btn-sm mt-2">
                    ⚙️ Gerenciar roles
                </a>
            @endif
        </div>

        {{-- Botões -}}
        <div class="mt-4">
            @if(!$somenteLeitura || $podeAlterarSenha)
                <button type="submit" class="btn btn-success">💾 Salvar alterações</button>
            @endif
            <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>
@endsection
--}}



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuário</h1>

    @php
        $auth = auth()->user();
        $schoolId = session('current_school_id');
        $roles = $usuario->roles->pluck('role_name')->toArray();

        $isNativo = $usuario->school_id == $schoolId;
        $isSelf = $usuario->id === $auth->id;
        $temRoleEscolaAuth = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();
        $temRoleEscolaAlvo = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $isVinculado = $usuario->roles()->wherePivot('school_id', $schoolId)->exists() && !$isNativo;
        $bloqueadoPorHierarquia = in_array('master', $roles) || in_array('secretaria', $roles);

        $somenteLeitura = (!$isNativo && !$isSelf) || $bloqueadoPorHierarquia || ($temRoleEscolaAuth && $temRoleEscolaAlvo);

    @endphp

    {{-- 🔹 Cabeçalho informativo -}}
    <div class="alert {{ $somenteLeitura ? 'alert-secondary' : 'alert-info' }}">
        <strong>🧾 Tipo de vínculo:</strong>
        @if($isSelf)
            <span>Você está editando sua própria conta.</span>
        @elseif($isNativo)
            <span>Usuário criado por esta escola.</span>
        @elseif($isVinculado)
            <span>Usuário apenas vinculado à sua escola.</span>
        @else
            <span>Usuário externo — não pertence nem está vinculado à sua escola.</span>
        @endif
    </div>

    {{-- 🚫 Bloqueio total se não tiver permissão -}}
    @if(!$isNativo && !$isSelf && !$isVinculado)
        <div class="alert alert-danger">
            🚫 Você não tem permissão para editar este usuário.
        </div>
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        @php return; @endphp
    @endif

    <form method="POST" action="{{ route('escola.usuarios.update', $usuario) }}">
        @csrf
        @method('PUT')

        {{-- Nome -}}
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome_u" class="form-control"
                   value="{{ old('nome_u', $usuario->nome_u) }}"
                   {{ $somenteLeitura ? 'readonly' : '' }}>
        </div>

        {{-- CPF -}}
        <div class="mb-3">
            <label class="form-label">CPF</label>
            <input type="text" class="form-control"
                   value="{{ $usuario->cpf }}" readonly>
        </div>

        {{-- Senha -}}
        @if($podeAlterarSenha)
        <div class="alert alert-info small py-1">
            🔐 Você pode alterar sua senha aqui. Deixe em branco se não quiser mudar.
        </div>
        @endif
        <div class="mb-3">
            <label class="form-label">Senha (preencha se desejar alterar)</label>
            <input type="password" name="senha" class="form-control"
                   {{ $somenteLeitura ? 'readonly' : '' }}>
        </div>

        {{-- Status -}}
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" {{ $somenteLeitura ? 'disabled' : '' }}>
                <option value="1" {{ $usuario->status ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ !$usuario->status ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>

        {{-- Roles agrupadas por escola -}}
        <div class="mb-4">
            <label class="form-label">Papéis (roles) por escola</label>
            @forelse($usuario->roles->groupBy('pivot.school_id') as $sid => $rolesGrupo)
                @php
                    $escola = \App\Models\Escola::find($sid);
                @endphp
                <div class="border rounded p-2 mb-2 bg-light">
                    <strong>{{ $escola->nome_e ?? 'Escola desconhecida' }}</strong>
                    <div class="mt-2">
                        @foreach($rolesGrupo as $r)
                            @php
                                $color = match($r->role_name) {
                                    'master' => 'danger',
                                    'secretaria' => 'primary',
                                    'escola' => 'info',
                                    'professor' => 'success',
                                    'aluno' => 'secondary',
                                    default => 'dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($r->role_name) }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhum papel atribuído.</p>
            @endforelse

            {{-- Botão para gerenciar roles -}}
            @if(Route::has('escola.usuarios.roles.edit'))
                <a href="{{ route('escola.usuarios.roles.edit', $usuario->id) }}"
                   class="btn btn-outline-primary btn-sm mt-2">
                    ⚙️ Gerenciar roles
                </a>
            @endif
        </div>

        {{-- Botões -}}
        @if(!$somenteLeitura)
            <button type="submit" class="btn btn-success">💾 Salvar alterações</button>
        @endif
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
--}}



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuário</h1>

    <form method="POST" action="{{ route('escola.usuarios.update', $usuario) }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome_u" class="form-control" value="{{ $usuario->nome_u }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">CPF</label>
            <input type="text" name="cpf" class="form-control" value="{{ $usuario->cpf }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nova Senha (deixe em branco para não alterar)</label>
            <input type="password" name="senha" class="form-control">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="1" {{ $usuario->status == 1 ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ $usuario->status == 0 ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Roles</label>
            @foreach($roles as $role)
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="roles[]"
                           value="{{ $role->id }}"
                           {{ $usuario->roles->contains($role->id) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $role->role_name }}</label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Salvar Alterações</button>
        <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
--}}