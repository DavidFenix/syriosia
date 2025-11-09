@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h4 mb-4">Login</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">CPF</label>
            <input type="text" name="cpf" value="{{ old('cpf') }}" class="form-control" required autofocus>
            @error('cpf') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required>
            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button class="btn btn-primary">Entrar</button>
    </form>
</div>
@endsection
