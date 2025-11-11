<!DOCTYPE html>
<html>
<body>

<h2>Teste com imagens base64</h2>

<p>Logo:</p>
@if($logoBase64)
    <img src="{{ $logoBase64 }}" width="80">
@else
    (logo não carregado)
@endif

<p>Foto:</p>
@if($fotoBase64)
    <img src="{{ $fotoBase64 }}" width="80">
@else
    (foto não carregada)
@endif

<hr>

<p>Fim da página de teste</p>

</body>
</html>
