@extends('layouts.app')

@section('content')
<div class="container">

    {{-- ⚠️ Alerta se usuário já existir --}}
    @if(session('usuario_existente'))
        <div class="alert alert-warning">
            <h5>⚠️ Usuário já existe no sistema</h5>
            <p>O CPF informado já está cadastrado. Você pode vincular este usuário à escola selecionada.</p>

            <form action="{{ route('master.usuarios.vincular', session('usuario_existente')) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="school_id" value="{{ old('school_id') }}">
                @if(old('roles'))
                    @foreach(old('roles') as $r)
                        <input type="hidden" name="roles[]" value="{{ $r }}">
                    @endforeach
                @endif
                <button type="submit" class="btn btn-primary btn-sm">
                    🔗 Vincular este usuário à escola
                </button>
                <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary btn-sm">
                    Cancelar
                </a>
            </form>
        </div>
    @endif

    <h2 class="mb-3">👤 Novo Usuário (Master)</h2>

    <form action="{{ route('master.usuarios.store') }}" method="POST" class="card card-body shadow-sm">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nome_u" class="form-label">Nome</label>
                <input type="text" name="nome_u" id="nome_u" value="{{ old('nome_u') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="cpf" class="form-label">CPF (login)</label>
                <input type="text" name="cpf" id="cpf" value="{{ old('cpf') }}" class="form-control" maxlength="11" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" minlength="6" required>
            </div>
            <div class="col-md-6">
                <label for="school_id" class="form-label">Escola</label>
                <select name="school_id" id="school_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    @foreach($escolas as $e)
                        <option value="{{ $e->id }}" {{ old('school_id') == $e->id ? 'selected' : '' }}>
                            {{ $e->nome_e }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Roles / Papéis</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="roles[]"
                               value="{{ $role->id }}"
                               id="role_{{ $role->id }}"
                               {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ ucfirst($role->role_name) }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success me-2">💾 Salvar</button>
            <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Novo Usuário</h1>

    <form method="POST" action="{{ route('master.usuarios.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nome*</label>
            <input type="text" name="nome_u" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">CPF*</label>
            <input type="text" name="cpf" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Senha*</label>
            <input type="password" name="senha" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Escola*</label>
            <select name="school_id" class="form-control">
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}">{{ $escola->nome_e }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Função(Papel/Destino)</label><br>
            @foreach($roles as $role)
                <input type="checkbox" name="roles[]" value="{{ $role->id }}"> {{ $role->role_name }}<br>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
--}}