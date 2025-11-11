@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">👤 Meu Perfil (Secretaria)</h2>

    {{-- Mensagens de sucesso ou erro --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('secretaria.usuarios.update', $usuario) }}" method="POST" class="card card-body shadow-sm">
        @csrf
        @method('PUT')

        {{-- 🔹 Informações gerais (somente leitura) --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" value="{{ $usuario->nome_u }}" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">CPF</label>
                <input type="text" class="form-control" value="{{ $usuario->cpf }}" readonly>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Escola de Origem</label>
            <input type="text" class="form-control" 
                   value="{{ $usuario->escola->nome_e ?? 'Não definida' }}" readonly>
        </div>

        {{-- 🔹 Roles agrupadas por escola --}}
        <div class="mb-4">
            <label class="form-label">Papéis (Roles) por Escola</label>
            @forelse($rolesPorEscola as $schoolId => $roles)
                @php
                    $escolaNome = \App\Models\Escola::find($schoolId)->nome_e ?? 'Escola desconhecida';
                @endphp
                <div class="border rounded p-2 mb-2 bg-light">
                    <strong>{{ $escolaNome }}</strong>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" checked disabled>
                                <label class="form-check-label">
                                    {{ ucfirst($role->role_name) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhuma role vinculada.</p>
            @endforelse

            {{-- ⚙️ Botão "Gerenciar roles" --}}
            @if(Route::has('secretaria.usuarios.roles.edit'))
                <a href="{{ route('secretaria.usuarios.roles.edit', $usuario->id) }}"
                   class="btn btn-outline-primary btn-sm mt-2">
                    ⚙️ Gerenciar roles
                </a>
            @endif
            
        </div>

        {{-- 🔹 Alterar senha --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="senha" class="form-label">Nova Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" minlength="6" required>
            </div>
            <div class="col-md-6">
                <label for="senha_confirmation" class="form-label">Confirmar Senha</label>
                <input type="password" name="senha_confirmation" id="senha_confirmation" class="form-control" minlength="6" required>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success me-2">
                💾 Atualizar Senha
            </button>
            <a href="{{ route('secretaria.usuarios.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </div>
    </form>
</div>
@endsection
