@php
    use App\Models\Escola;

    $schoolId = session('current_school_id');
    $escola = Escola::find($schoolId);

    // 1. Pegamos a URL pública da logo
    $logoWeb = syrios_school_logo($schoolId);

    // 2. Convertendo a URL para caminho relativo correto (compatível com ambos Railway e InfinityFree)
    $relativeLogo = syrios_url_to_storage_relative($logoWeb);

    // 3. Convertendo o relativo para caminho físico absoluto
    $logoUrl = storage_syrios_path($relativeLogo);

@endphp

{{-- ===================== CABEÇALHO DINÂMICO DA ESCOLA ===================== --}}
    <div class="text-center mb-4">
        <img src="{{ syrios_school_logo($schoolId) }}" alt="Logo da Escola" style="width:80px; height:80px; object-fit:contain; border-radius:8px;">

        <h4 class="mb-1">{{ $escola->nome_e ?? 'Nome da Escola' }}</h4>

        <p class="text-muted fst-italic small">
              "{{ $escola->frase_efeito ?? '' }}"
        </p>

        <hr class="mt-3 mb-4">
    </div>