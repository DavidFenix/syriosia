@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Gerenciar Roles de {{ $usuario->nome_u }}</h1>

    {{-- 🔹 Contexto --}}
    <div class="alert alert-info">
        <strong>Escola atual:</strong> {{ $escolaAtual->nome_e ?? 'Desconhecida' }}<br>
        <strong>Usuário:</strong> {{ $usuario->nome_u }} (CPF: {{ $usuario->cpf }})
    </div>

    {{-- 🔸 Visão geral --}}
    @if($usuario->roles->isNotEmpty())
        <div class="card mb-4 border border-primary shadow-sm">
            <div class="card-header bg-primary text-white">📊 Visão geral de roles</div>
            <div class="card-body p-2">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Escola</th><th>Roles</th></tr></thead>
                    <tbody>
                        @foreach($usuario->roles->groupBy('pivot.school_id') as $sid => $rolesGrupo)
                            @php $escola = \App\Models\Escola::find($sid); @endphp
                            <tr>
                                <td>{{ $escola->nome_e ?? '—' }}</td>
                                <td>
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 🔹 Formulário --}}
    <form method="POST" action="{{ route('escola.usuarios.roles.update', $usuario->id) }}">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>🧩 Papéis disponíveis nesta escola</strong>
            </div>
            <div class="card-body">
                @php
                    $auth = auth()->user();
                    $authIsSame = $auth->id === $usuario->id;
                @endphp

                @foreach($roles as $role)
                    @php
                        // protege roles inalteráveis
                        $isProtected = in_array($role->role_name, ['master', 'secretaria', 'escola']);
                        $isChecked = in_array($role->id, $rolesSelecionadas);
                    @endphp

                    <div class="form-check mb-1">
                        <input type="checkbox"
                               class="form-check-input"
                               name="roles[]"
                               value="{{ $role->id }}"
                               id="role_{{ $role->id }}"
                               {{ $isChecked ? 'checked' : '' }}
                               {{ $isProtected ? 'disabled' : '' }}>

                        <label class="form-check-label {{ $isProtected ? 'text-muted' : '' }}"
                               for="role_{{ $role->id }}">
                            {{ ucfirst($role->role_name) }}
                            @if($isProtected)
                                <small class="text-muted">(protegida)</small>
                            @endif
                        </label>
                    </div>
                @endforeach


                {{--
                @foreach($roles as $role)
                    @php
                        $checked = in_array($role->id, $rolesSelecionadas);
                        $isRestrita = in_array($role->role_name, ['master', 'secretaria']);
                        $disabled = $isRestrita ||
                            ($authIsSame && $role->role_name === 'escola' && $checked);
                    @endphp

                    <div class="form-check mb-2">
                        <input type="checkbox"
                               class="form-check-input"
                               name="roles[]"
                               value="{{ $role->id }}"
                               id="role_{{ $role->id }}"
                               {{ $checked ? 'checked' : '' }}
                               {{ $disabled ? 'disabled' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ ucfirst($role->role_name) }}
                            @if($disabled)
                                <span class="text-muted small">(protegida)</span>
                            @endif
                        </label>
                    </div>
                @endforeach
                --}}
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">💾 Salvar alterações</button>
            <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary ms-2">Voltar</a>
        </div>
    </form>

    <p class="text-muted mt-4 small">
        💡 Roles restritas (<strong>master</strong>, <strong>secretaria</strong>) são geridas apenas pela Secretaria ou Master.
    </p>
</div>
@endsection

