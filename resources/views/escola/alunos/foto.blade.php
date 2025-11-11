@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">📸 Atualizar Foto — {{ $aluno->matricula.'::'.$aluno->nome_a }}</h2>

    <div class="row">
        <div class="col-md-4 text-center">
            <img id="fotoPreview" src="{{ $fotoUrl }}" 
                 class="rounded-circle shadow mb-3"
                 style="width:180px;height:180px;object-fit:cover;border:4px solid #ccc;">
            <p class="text-muted">Foto atual</p>
        </div>

        <div class="col-md-8">
            <form action="{{ route('escola.alunos.foto.update', $aluno->id) }}" 
                  method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nova Foto (PNG ou JPG)</label>
                    <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*" required>
                    <div class="form-text">
                        Recomendado: 220×220 px — até 500 KB.
                    </div>
                </div>

                <div class="mb-3" id="cropContainer" style="display:none;">
                    <div style="max-width:300px;margin:auto;">
                        <img id="cropperImage" style="width:100%;">
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">
                    💾 Salvar Foto
                </button>
                <a href="{{ route('escola.alunos.index') }}" class="btn btn-secondary mt-3">↩ Voltar</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Cropper.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<script>
let cropper;
document.getElementById('fotoInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
        const image = document.getElementById('cropperImage');
        image.src = event.target.result;
        document.getElementById('cropContainer').style.display = 'block';

        // Destroi o cropper antigo se já existir
        if (cropper) cropper.destroy();

        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 2,
            dragMode: 'move',
            background: false,
            guides: false,
            autoCropArea: 1,
            cropBoxResizable: true
        });
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
