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

    
{{--
<table width="100%" cellspacing="0" cellpadding="0"
       style="border-bottom: 1px solid #333; margin-bottom: 12px; padding-bottom: 8px;">
  <tr>
    <td width="80" align="center">
      <img src="{{ $logoUrl }}" alt="Logo" style="width:70px;height:70px;object-fit:contain;">
    </td>
    <td align="center" style="font-family: DejaVu Sans, sans-serif;">
      <div style="font-size:16px;font-weight:bold;">
        {{ $escola->nome_e ?? 'Escola não identificada' }}
      </div>
      @if(!empty($escola->frase_efeito))
        <div style="font-size:12px;color:#555;">
          <em>{{ $escola->frase_efeito }}</em>
        </div>
      @endif
    </td>
  </tr>
</table>
--}}