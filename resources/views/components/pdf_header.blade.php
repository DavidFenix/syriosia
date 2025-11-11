@php
    use App\Models\Escola;

    $schoolId = session('current_school_id');
    $escola = Escola::find($schoolId);

    $logoUrl = null;
    if ($escola && $escola->logo_path && file_exists(public_path('storage/'.$escola->logo_path))) {
        $logoUrl = public_path('storage/'.$escola->logo_path);
    } else {
        $logoUrl = public_path('storage/img-user/padrao.png');
    }
@endphp


{{-- ===================== CABEÇALHO DINÂMICO DA ESCOLA ===================== --}}
    <div class="text-center mb-4">
        @if($escola->logo_path && file_exists(public_path('storage/'.$escola->logo_path)))
          <img src="{{ asset('storage/'.$escola->logo_path) }}" 
               alt="Logo da Escola"
               style="width:80px; height:80px; object-fit:contain; border-radius:8px;">
          <h4 class="mb-1">{{ $escola->nome_e ?? 'Nome da Escola' }}</h4>
          <p class="text-muted fst-italic small">
                "{{ $escola->frase_efeito ?? '' }}"
          </p>
        @else
          <img src="{{ asset('storage/img-user/padrao.png') }}" 
               alt="Logo padrão"
               style="width:80px; height:80px; object-fit:contain; border-radius:8px;">
          <h4 class="mb-1">{{ $escola->nome_e ?? 'Nome da Escola' }}</h4>
          <p class="text-muted fst-italic small">
                "{{ $escola->frase_efeito ?? '' }}"
          </p>
        @endif
        <hr class="mt-3 mb-4">
    </div>