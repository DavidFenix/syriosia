@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">✏️ Editar Usuário</h1>
    
    <form method="POST" action="{{ route('master.usuarios.update', $usuario) }}" class="card card-body shadow-sm">
        @csrf
        @method('PUT')

        {{-- ===================================================
             DADOS BÁSICOS
        =================================================== --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Nome*</label>
                <input type="text" name="nome_u" class="form-control" 
                       value="{{ old('nome_u', $usuario->nome_u) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">CPF*</label>
                <input type="text" name="cpf" class="form-control" 
                       value="{{ old('cpf', $usuario->cpf) }}" required maxlength="20">
            </div>
        </div>

        {{-- ===================================================
             REGRAS GERAIS:
             - Super Master nunca pode ser desativado nem mudar de escola
             - Super Master pode alterar todos, exceto a si mesmo
             - Master comum pode alterar usuários comuns
             - Roles são apenas informativas
        =================================================== --}}

        @php
            $auth = auth()->user();
            $isSelf = $auth && $auth->id === $usuario->id;
            $authIsMaster = $auth->hasRole('master');
            $authIsSuper = $auth->is_super_master;
            $targetIsSuper = $usuario->is_super_master;
            $targetIsMaster = $usuario->roles->pluck('role_name')->contains('master');
        @endphp

        {{-- ===================================================
             STATUS
        =================================================== --}}
        <div class="row mb-3">
          <div class="col-md-6">
              <label class="form-label">Senha (preencha apenas se quiser trocar)</label>
              <input type="password" name="senha" class="form-control">
          </div>

          <div class="col-md-6">
              <label class="form-label">Status*</label>

              {{-- 🔒 Super Master nunca desativado --}}
              @if($targetIsSuper)
                  <select class="form-select" disabled>
                      <option selected>Ativo</option>
                  </select>
                  <input type="hidden" name="status" value="1">

              {{-- 🔒 Master comum não pode desativar super/master --}}
              @elseif($authIsMaster && !$authIsSuper && ($targetIsMaster || $targetIsSuper))
                  <select class="form-select" disabled>
                      <option value="{{ $usuario->status }}">
                          {{ $usuario->status ? 'Ativo' : 'Inativo' }}
                      </option>
                  </select>
                  <input type="hidden" name="status" value="{{ $usuario->status }}">

              {{-- 🔓 Master comum pode ativar/desativar usuários comuns --}}
              @elseif($authIsMaster && !$authIsSuper)
                  <select name="status" class="form-select">
                      <option value="1" {{ old('status', $usuario->status) == 1 ? 'selected' : '' }}>Ativo</option>
                      <option value="0" {{ old('status', $usuario->status) == 0 ? 'selected' : '' }}>Inativo</option>
                  </select>

              {{-- 🔓 Super Master pode alterar todos, menos ele mesmo --}}
              @elseif($authIsSuper && !$isSelf)
                  <select name="status" class="form-select">
                      <option value="1" {{ old('status', $usuario->status) == 1 ? 'selected' : '' }}>Ativo</option>
                      <option value="0" {{ old('status', $usuario->status) == 0 ? 'selected' : '' }}>Inativo</option>
                  </select>

              {{-- 🧍 Super Master não pode desativar a si mesmo --}}
              @elseif($authIsSuper && $isSelf)
                  <select class="form-select" disabled>
                      <option selected>Ativo</option>
                  </select>
                  <input type="hidden" name="status" value="1">

              {{-- fallback --}}
              @else
                  <select class="form-select" disabled>
                      <option value="{{ $usuario->status }}">
                          {{ $usuario->status ? 'Ativo' : 'Inativo' }}
                      </option>
                  </select>
                  <input type="hidden" name="status" value="{{ $usuario->status }}">
              @endif
          </div>
        </div>

        {{-- ===================================================
             ESCOLA DE ORIGEM
        =================================================== --}}
        <div class="mb-4">
          <label class="form-label">Escola de Origem</label>

          @php
              $escolaUsuario = $escolas->firstWhere('id', old('school_id', $usuario->school_id));
          @endphp

          {{-- 🔒 Super Master alvo: escola fixa --}}
          @if($targetIsSuper)
              <input type="text" class="form-control" value="{{ $escolaUsuario->nome_e ?? 'Desconhecida' }}" disabled>
              <input type="hidden" name="school_id" value="{{ $usuario->school_id }}">

          {{-- 🔒 Master comum não pode mudar escola de super/master --}}
          @elseif($authIsMaster && !$authIsSuper && ($targetIsMaster || $targetIsSuper))
              <input type="text" class="form-control" value="{{ $escolaUsuario->nome_e ?? 'Desconhecida' }}" disabled>
              <input type="hidden" name="school_id" value="{{ $usuario->school_id }}">

          {{-- 🔓 Master comum pode mudar escola de usuários comuns --}}
          @elseif($authIsMaster && !$authIsSuper)
              <select name="school_id" id="school_id" class="form-select">
                  <option value="">Selecione...</option>
                  @foreach($escolas as $escola)
                      <option value="{{ $escola->id }}" {{ old('school_id', $usuario->school_id) == $escola->id ? 'selected' : '' }}>
                          {{ $escola->nome_e }}
                      </option>
                  @endforeach
              </select>

          {{-- 🧍 Super Master não pode mudar a própria escola --}}
          @elseif($authIsSuper && $isSelf)
              <input type="text" class="form-control" value="{{ $escolaUsuario->nome_e ?? 'Desconhecida' }}" disabled>
              <input type="hidden" name="school_id" value="{{ $usuario->school_id }}">

          {{-- 🔓 Super Master pode mudar escola de qualquer outro --}}
          @elseif($authIsSuper && !$isSelf)
              <select name="school_id" id="school_id" class="form-select">
                  <option value="">Selecione...</option>
                  @foreach($escolas as $escola)
                      <option value="{{ $escola->id }}" {{ old('school_id', $usuario->school_id) == $escola->id ? 'selected' : '' }}>
                          {{ $escola->nome_e }}
                      </option>
                  @endforeach
              </select>

          {{-- fallback --}}
          @else
              <input type="text" class="form-control" value="{{ $escolaUsuario->nome_e ?? 'Desconhecida' }}" disabled>
              <input type="hidden" name="school_id" value="{{ $usuario->school_id }}">
          @endif
        </div>

        {{-- ===================================================
             ROLES (informativas, sem edição)
        =================================================== --}}
        <div class="mb-4">
          <label class="form-label">Funções (Roles)</label>
          <div class="border rounded p-2 bg-light">
              @foreach($rolesUsuario as $roleId)
                  @php
                      $role = $roles->firstWhere('id', $roleId);
                  @endphp
                  <span class="badge bg-secondary me-1">{{ $role->role_name ?? 'Desconhecida' }}</span>
              @endforeach
              <small class="text-muted d-block mt-2">
                  As funções são apenas informativas e não podem ser alteradas aqui.
              </small>
          </div>
        </div>

        {{-- ===================================================
             PAPÉIS AGRUPADOS POR ESCOLA
        =================================================== --}}
        <div class="mb-4">
            <h5 class="mb-3">🧩 Papéis (Roles) - Somente leitura</h5>

            @php
                $rolesPorEscola = $usuario->roles->groupBy('pivot.school_id');
            @endphp

            @foreach($rolesPorEscola as $schoolId => $rolesGrupo)
                <div class="border rounded p-3 mb-3 bg-light">
                    <strong>Escola: {{ optional($escolas->firstWhere('id', $schoolId))->nome_e ?? 'Desconhecida' }}</strong>
                    <div class="mt-2">
                        @foreach($roles as $role)
                            @php
                                $checked = $rolesGrupo->pluck('id')->contains($role->id);
                            @endphp
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       type="checkbox"
                                       value="{{ $role->id }}"
                                       id="role_{{ $schoolId }}_{{ $role->id }}"
                                       {{ $checked ? 'checked' : '' }}
                                       disabled>
                                <label class="form-check-label" for="role_{{ $schoolId }}_{{ $role->id }}">
                                    {{ ucfirst($role->role_name) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($rolesPorEscola->isEmpty())
                <div class="alert alert-info">
                    Este usuário ainda não possui papéis atribuídos.  
                    Os papéis são apenas informativos e devem ser gerenciados em outro módulo.
                </div>
            @endif
        </div>

        {{-- ===================================================
             AÇÕES
        =================================================== --}}
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success me-2">💾 Salvar Alterações</button>
            <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>
</div>
@endsection
