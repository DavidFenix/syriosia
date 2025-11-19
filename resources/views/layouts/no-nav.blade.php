<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syrios - Painel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body>


{{-- Debug de mensagens -}}
@if ($errors->any())
    <div class="alert alert-danger m-5 fs-4">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success m-5">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger m-5">
        {{ session('error') }}
    </div>
@endif
--}}

<div class="container">
    @yield('content')
</div>

{{-- ✅ jQuery primeiro --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

{{-- ✅ Depois Bootstrap --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- ✅ DataTables (depois do jQuery e do Bootstrap) --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

{{--🔎 Exportar Excel/PDF: se você quer manter os botões “Excel” e “PDF”, garanta que esses 3 scripts também estejam no seu app.blade.php antes de buttons.html5.min.js:--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>


{{-- ✅ Script local de inicialização --}}
<script src="{{ asset('js/datatables-init.js') }}"></script>

{{-- ✅ Scripts adicionados via @push('scripts') nos blades --}}
@stack('scripts')

</body>
</html>


