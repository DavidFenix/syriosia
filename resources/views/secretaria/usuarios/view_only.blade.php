@extends('layouts.app')
@section('content')
<div class="container">
    <h2>👀 Visualização de Secretário</h2>

    <div class="card card-body shadow-sm">
        <p><strong>Nome:</strong> {{ $usuario->nome_u }}</p>
        <p><strong>CPF:</strong> {{ $usuario->cpf }}</p>
        <p><strong>Escola de Origem:</strong> {{ $usuario->escola->nome_e ?? '-' }}</p>

        <h5>Roles por Escola</h5>
        @foreach($rolesPorEscola as $schoolId => $roles)
            <div class="border rounded p-2 mb-2">
                <strong>{{ \App\Models\Escola::find($schoolId)->nome_e ?? 'Escola desconhecida' }}</strong><br>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" checked disabled>
                            <label class="form-check-label">{{ ucfirst($role->role_name) }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <a href="{{ route('secretaria.usuarios.index') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</div>
@endsection
