@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>🏫 Identidade da Escola</h2>
    <p class="text-muted">Personalize a identidade visual e frase institucional da sua escola.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('escola.identidade.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="frase_efeito" class="form-label">Frase institucional</label>
            <input type="text" id="frase_efeito" name="frase_efeito"
                   value="{{ old('frase_efeito', $escola->frase_efeito) }}"
                   class="form-control" maxlength="255"
                   placeholder="Ex: Educar é transformar vidas.">
        </div>

        <div class="mb-3">
            <label for="logo" class="form-label">Logotipo</label>
            <input type="file" id="logo" name="logo" class="form-control" accept="image/*">

            @if($escola->logo_path)
                <div class="mt-3">
                    <p>Logo atual:</p>
                    <img src="{{ asset('storage/'.$escola->logo_path) }}" 
                         alt="Logo atual" class="img-thumbnail" style="max-height:100px;">
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
    </form>
</div>
@endsection
