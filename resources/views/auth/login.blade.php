@extends('layouts.no-nav')

@section('content')
<div class="login-bg d-flex justify-content-center align-items-center min-vh-100">

    <div class="login-card shadow-lg p-4 rounded-4 bg-white animate__animated animate__fadeIn">
        
        {{-- Foto circular --}}
        <div class="text-center mb-3">

            @php
                $logoStorage = public_path('storage/logos/syrios.png');
                $logoUrl = file_exists($logoStorage)
                    ? asset('storage/logos/syrios.png')
                    : asset('images/default-logo.png');  // fallback real
            @endphp

            <img src="{{ $logoUrl }}" width="150" height="150" alt="Logo Syrios">
        </div>


        {{-- Título --}}
        <h3 class="text-center fw-bold mb-4 text-white">
            {{ config('app.name', 'Syrios') }}
        </h3>

        {{-- Formulário --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Campo CPF --}}
            <div class="mb-1">
                <input type="text"
                       name="cpf"
                       value="{{ old('cpf') }}"
                       class="form-control input-login-top @error('cpf') is-invalid @enderror fs-4"
                       placeholder="CPF"
                       required autofocus>

                @error('cpf')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            {{-- Campo Senha com olho --}}
            <div class="mb-3 position-relative fs-5">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-control input-login-bottom @error('password') is-invalid @enderror fs-4"
                       placeholder="Senha"
                       required>

                {{-- Ícone olho --}}
                <span class="position-absolute top-50 end-0 translate-middle-y me-3"
                      style="cursor: pointer;"
                      onclick="togglePassword()">
                    👁️
                </span>

                @error('password')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            {{-- CPF -}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf') }}" class="form-control form-control-lg" required autofocus>
                    @error('cpf') 
                        <small class="text-danger">{{ $message }}</small> 
                    @enderror
                </div>

                {{-- Senha -}}
                <div class="mb-3 position-relative">
                    <label class="form-label fw-semibold">Senha</label>

                    <input type="password" name="password" id="passwordField" class="form-control form-control-lg" required>

                    <span class="password-toggle" onclick="togglePassword()">
                        👁
                    </span>

                    @error('password') 
                        <small class="text-danger">{{ $message }}</small> 
                    @enderror
            </div>--}}

            {{-- Botão Entrar --}}
            <button class="btn btn-primary w-100 btn-lg rounded-3 fw-semibold py-3 fs-4">
                Entrar
            </button>
        </form>

        {{-- Rodapé --}}
        <div class="text-center mt-4 text-muted small fs-6">
            © 2025 — {{ config('app.school_name', 'Syrios') }}<br>
            Todos os direitos reservados.
        </div>

    </div>

</div>
@endsection

@push('styles')
<style>
    .input-login-top {
        height: 70px;
        /*font-size: 1.25rem;*/
        border-radius: 10px 10px 0 0; /* só superior arredondado */
    }

    .input-login-bottom {
        height: 70px;
        /*font-size: 1.25rem;*/
        border-radius: 0 0 10px 10px; /* só inferior arredondado */
        /*border-top: none; /* unir com o campo acima */*/
    }

    /* Quando focar, visual mais bonito */
    .input-login-top:focus,
    .input-login-bottom:focus {
        box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, .25);
    }

    .text-danger {
        color: #fff !important;            /* texto branco */
        font-size: 1.1rem;                 /* maior */
        font-weight: 600;                  /* mais forte */
        text-shadow: 0 0 4px rgba(0,0,0,0.8); /* contorno simulando stroke */
        display: block;
        margin-top: 4px;
        /* contorno vermelho usando text-shadow */
        text-shadow: 
            -1px -1px 0 #ff0000,
             1px -1px 0 #ff0000,
            -1px  1px 0 #ff0000,
             1px  1px 0 #ff0000;
    }
</style>
@endpush


@push('styles')
<style>
    
    body {
        /*background: #0b2348; /* azul escuro elegante */*/
        min-height: 100vh;   /* garante que cobre a tela inteira */
        margin: 0;
        /*background: linear-gradient(135deg, #004e92 0%, #000428 100%);*/
        /*background: linear-gradient(135deg, #004e92 0%, #000428 100%);*/
        background: linear-gradient(60deg, white, gray); width:100%;
    }
   
    .login-bg {
        /*background: linear-gradient(135deg, #004e92 0%, #000428 100%);
        padding: 20px;*/
    }

    .login-card {
        width: 100%;
        max-width: 420px;
        border-radius: 18px;
        /*background: linear-gradient(135deg, #004e92 0%, #000428 100%)*/
        background: linear-gradient(60deg, #26c6da, #00f); width:100%;
    }

    .password-toggle {
        position: absolute;
        top: 38px;
        right: 12px;
        cursor: pointer;
        font-size: 20px;
        opacity: 0.6;
    }
    .password-toggle:hover {
        opacity: 1;
    }

</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const field = document.getElementById('passwordField');
    field.type = (field.type === 'password') ? 'text' : 'password';
}
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush

