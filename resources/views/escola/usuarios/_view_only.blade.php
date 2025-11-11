Exclua esse aquivo que não está sendo usado
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>👁️ Visualização de Usuário</h1>

    {{-- Mensagem de alerta opcional --}}
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="mb-3">📋 Dados do Usuário</h4>

            <div class="mb-2"><strong>Nome:</strong> {{ $usuario->nome_u }}</div>
            <div class="mb-2"><strong>CPF:</strong> {{ $usuario->cpf }}</div>
            <div class="mb-2">
                <strong>Status:</strong>
                @if($usuario->status)
                    <span class="badge bg-success">Ativo</span>
                @else
                    <span class="badge bg-danger">Inativo</span>
                @endif
            </div>
            <div class="mb-2">
                <strong>Escola de origem:</strong>
                {{ optional($usuario->escola)->nome_e ?? '—' }}
            </div>
        </div>
    </div>

    {{-- Roles agrupadas por escola --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">🎓 Papéis (roles) vinculados</h5>

            @forelse($rolesPorEscola as $escola => $rolesGrupo)
                <div class="border rounded p-2 mb-2 bg-light">
                    <strong>{{ $escola }}</strong>
                    <div class="mt-2">
                        @foreach($rolesGrupo as $r)
                            @php
                                $color = match($r->role_name) {
                                    'master' => 'danger',
                                    'secretaria' => 'primary',
                                    'escola' => 'info',
                                    'professor' => 'success',
                                    'aluno' => 'secondary',
                                    default => 'light'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($r->role_name) }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhum papel vinculado.</p>
            @endforelse
        </div>
    </div>

    {{-- Botão Gerenciar roles (somente se autorizado) --}}
    @php
        $auth = auth()->user();
        $self = $auth->id === $usuario->id;
        $schoolId = session('current_school_id');
        $authTemRoleEscola = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();
    @endphp

    @if($self || $authTemRoleEscola)
        <a href="{{ route('escola.usuarios.roles.edit', $usuario->id) }}"
           class="btn btn-outline-primary btn-sm mt-3">
            ⚙️ Gerenciar roles
        </a>
    @endif

    <a href="{{ route('escola.usuarios.index') }}" class="btn btn-secondary mt-3">⬅️ Voltar</a>
</div>
@endsection
