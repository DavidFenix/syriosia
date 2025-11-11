@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Painel da Secretaria - Usuários</h1>
</div>

<div class="container">
    <h1>Usuários das Escolas Filhas - {{ $secretaria->nome_e }}</h1>

    <a href="{{ route('secretaria.usuarios.create') }}" class="btn btn-primary mb-3">Novo Usuário</a>

    <table class="table table-bordered table-striped align-middle" id="tabela-usuarios">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Escola</th>
                <th>Roles</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>

        <tbody>
        @php
            $currentUser = auth()->user();
            $currentSchoolId = session('current_school_id');
        @endphp

        @foreach($usuarios as $usuario)
            @foreach($usuario->roles as $role)
                @php
                    $pivotSchoolId   = $role->pivot->school_id ?? $usuario->school_id;
                    $escolaVinculo   = \App\Models\Escola::find($pivotSchoolId);
                    $isVinculado     = $usuario->school_id !== $pivotSchoolId;

                    // 🧱 Regra: o vínculo atual é a role de "secretaria" na escola ativa
                    $isSelfSecretaria = (
                        $usuario->id === $currentUser->id &&
                        $pivotSchoolId == $currentSchoolId &&
                        $role->role_name === 'secretaria'
                    );

                    // 🧱 Regra: é outro secretário da mesma secretaria ativa
                    $isColegaSecretaria = (
                        $usuario->id !== $currentUser->id &&
                        $role->role_name === 'secretaria' &&
                        $pivotSchoolId == $currentSchoolId
                    );

                    // 🔒 Só bloqueia se o vínculo for de role "secretaria" na escola ativa
                    $naoPodeExcluir = $isSelfSecretaria || $isColegaSecretaria;
                @endphp

                <tr @if($isSelfSecretaria) class="table-warning fw-bold" @endif>
                    <td>{{ $usuario->id }}</td>
                    <td>
                        {{ $usuario->nome_u }}
                        @if($isSelfSecretaria)
                            <span class="text-muted ms-1">(você)</span>
                        @endif
                    </td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ $escolaVinculo->nome_e ?? '-' }}</td>
                    <td>
                        @php
                            $badgeClass = $role->role_name === 'secretaria' ? 'bg-warning text-dark' : 'bg-secondary';
                        @endphp

                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($role->role_name) }}
                            @if($isSelfSecretaria)
                                🏛️
                            @endif
                        </span>

                        @if($isVinculado)
                            <span class="badge bg-info">🔗 Vinculado</span>
                        @endif
                    </td>

                    <td class="text-end">
                        <a href="{{ route('secretaria.usuarios.edit', $usuario) }}" 
                           class="btn btn-sm btn-outline-secondary">Editar</a>

                        {{-- 🔒 Cadeado para vínculos protegidos --}}
                        @if($naoPodeExcluir)
                            <button class="btn btn-sm btn-outline-secondary" disabled title="Você não pode excluir este vínculo de Secretaria.">
                                🔒
                            </button>
                        @else
                            <form action="{{ route('secretaria.usuarios.destroy', $usuario) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Excluir este vínculo/usuário?');">
                                @csrf @method('DELETE')
                                {{-- Passa a escola e role que o usuário deseja excluir --}}
                                <input type="hidden" name="school_id" value="{{ $pivotSchoolId }}">
                                <input type="hidden" name="role_id" value="{{ $role->id }}">
                                <button class="btn btn-sm btn-outline-danger" title="Excluir vínculo">
                                    🗑️
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>


        {{--
        <tbody>
        @php
            $currentUser = auth()->user();
            $currentSchoolId = session('current_school_id');
        @endphp

        @foreach($usuarios as $usuario)
            @foreach($usuario->roles as $role)
                @php
                    $pivotSchoolId = $role->pivot->school_id ?? $usuario->school_id;
                    $escolaVinculo = \App\Models\Escola::find($pivotSchoolId);
                    $isVinculado = $usuario->school_id !== $pivotSchoolId;

                    // É o usuário logado com role secretaria ativa nesta escola
                    $isSelfSecretaria = (
                        $usuario->id === $currentUser->id &&
                        $pivotSchoolId == $currentSchoolId &&
                        $role->role_name === 'secretaria'
                    );

                    // É outro secretário atuando nesta secretaria
                    $isColegaSecretaria = (
                        $usuario->id !== $currentUser->id &&
                        $role->role_name === 'secretaria' &&
                        $pivotSchoolId == $currentSchoolId
                    );

                    // Se não pode excluir
                    $naoPodeExcluir = $isSelfSecretaria || $isColegaSecretaria;
                @endphp

                <tr @if($isSelfSecretaria) class="table-warning fw-bold" @endif>
                    <td>{{ $usuario->id }}</td>
                    <td>
                        {{ $usuario->nome_u }}
                        @if($isSelfSecretaria)
                            <span class="text-muted ms-1">(você)</span>
                        @endif
                    </td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ $escolaVinculo->nome_e ?? '-' }}</td>
                    <td>
                        @php
                            $badgeClass = $role->role_name === 'secretaria' ? 'bg-warning text-dark' : 'bg-secondary';
                        @endphp

                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($role->role_name) }}
                            @if($isSelfSecretaria)
                                🏛️
                            @endif
                        </span>

                        @if($isVinculado)
                            <span class="badge bg-info">🔗 Vinculado</span>
                        @endif
                    </td>

                    <td class="text-end">
                        <a href="{{ route('secretaria.usuarios.edit', $usuario) }}" 
                           class="btn btn-sm btn-outline-secondary">Editar</a>

                        @if($naoPodeExcluir)
                            <button class="btn btn-sm btn-outline-secondary" disabled title="Você não pode excluir este vínculo.">
                                🔒
                            </button>
                        @else
                            <form action="{{ route('secretaria.usuarios.destroy', $usuario) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Excluir este vínculo/usuário?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>

        {{--
        <tbody>
        @php
            $currentUser = auth()->user();
            $currentSchoolId = session('current_school_id');
        @endphp

        @foreach($usuarios as $usuario)
            @foreach($usuario->roles as $role)
                @php
                    $pivotSchoolId = $role->pivot->school_id ?? $usuario->school_id;
                    $escolaVinculo = \App\Models\Escola::find($pivotSchoolId);
                    $isVinculado = $usuario->school_id !== $pivotSchoolId;

                    // Verifica se é o usuário logado na secretaria atual
                    $isCurrentSecretaria = (
                        $usuario->id === $currentUser->id &&
                        $pivotSchoolId == $currentSchoolId &&
                        $role->role_name === 'secretaria'
                    );
                @endphp
                <tr @if($isCurrentSecretaria) class="table-warning fw-bold" @endif>
                    <td>{{ $usuario->id }}</td>
                    <td>
                        {{ $usuario->nome_u }}
                        @if($isCurrentSecretaria)
                            <span class="text-muted ms-1">(você)</span>
                        @endif
                    </td>
                    <td>{{ $usuario->cpf }}</td>
                    <td>{{ $escolaVinculo->nome_e ?? '-' }}</td>
                    <td>
                        @php
                            $badgeClass = $role->role_name === 'secretaria' ? 'bg-warning text-dark' : 'bg-secondary';
                        @endphp

                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($role->role_name) }}
                            @if($isCurrentSecretaria)
                                🏛️
                            @endif
                        </span>

                        @if($isVinculado)
                            <span class="badge bg-info">🔗 Vinculado</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('secretaria.usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="{{ route('secretaria.usuarios.destroy', $usuario) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este vínculo/usuário?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
        --}}


    </table>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    });
</script>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    // inicializa com o script global do public/js/datatables-init.js
    // colunas filtráveis: Nome(1), CPF(2), Escola(3), Roles(4), CNPJ(5)
    initDataTable('#tabela-usuarios', { order: [[1, 'asc']] }, [1, 2, 3, 4]);
});
</script>
@endpush





{{--
@extends('layouts.app')
@section('title','Usuários da Secretaria')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Usuários</h1>
  <a href="{{ route('secretaria.usuarios.create') }}" class="btn btn-primary">Novo usuário</a>
</div>

<table class="table table-sm table-striped align-middle">
  <thead>
    <tr>
      <th>#</th>
      <th>Nome</th>
      <th>CPF</th>
      <th>Escola</th>
      <th>Roles</th>
      <th class="text-end">Ações</th>
    </tr>
  </thead>
  <tbody>
  @forelse($usuarios as $u)
    <tr>
      <td>{{ $u->id }}</td>
      <td>{{ $u->nome_u }}</td>
      <td>{{ $u->cpf }}</td>
      <td>{{ $u->escola->nome_e }}</td>
      <td>
        @foreach($u->roles as $r)
          <span class="badge bg-info text-dark">{{ $r->role_name }}</span>
        @endforeach
      </td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('secretaria.usuarios.edit', $u) }}">Editar</a>
        <form action="{{ route('secretaria.usuarios.destroy', $u) }}" method="post" class="d-inline" onsubmit="return confirm('Excluir este usuário?');">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-outline-danger">Excluir</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center text-muted">Nenhum usuário cadastrado.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
--}}