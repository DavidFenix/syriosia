@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3">🧹 Limpeza de Imagens Órfãs</h2>

    {{-- ✅ Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    {{-- 🔍 Filtros --}}
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-4">
            <label for="school_id" class="form-label">🏫 Escola</label>
            <select name="school_id" id="school_id" class="form-select">
                <option value="">— Todas —</option>
                @foreach($escolas as $e)
                    <option value="{{ $e->id }}" {{ $filtroEscola == $e->id ? 'selected' : '' }}>
                        {{ $e->nome_e }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="pasta" class="form-label">📁 Pasta</label>
            <input type="text" name="pasta" id="pasta" class="form-control"
                   value="{{ $filtroPasta }}" placeholder="Ex: img-user/2025">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-outline-primary">🔍 Filtrar</button>
            <a href="{{ route('master.imagens.index') }}" class="btn btn-outline-secondary">Limpar</a>
        </div>
    </form>

    {{-- 📋 Resultados --}}
    @if(empty($orfas))
        <div class="alert alert-success">
            ✅ Nenhuma imagem órfã encontrada.
        </div>
    @else
        <form action="{{ route('master.imagens.limpar') }}" method="POST">
            @csrf

            <div class="alert alert-warning">
                <strong>⚠️ Atenção:</strong> As imagens abaixo não possuem aluno associado.<br>
                Filtradas {{ $filtroEscola ? 'para a escola selecionada' : 'em todas as escolas' }}.
            </div>

            @foreach($orfas as $schoolId => $lista)
                @php
                    // Garante que $lista seja sempre um array
                    $lista = is_array($lista) ? $lista : [$lista];
                @endphp
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <span>

                            @if($schoolId === 'sem_id')
                                🗂️ Imagens fora do padrão de nome (sem ID de escola)
                            @else
                                🏫 Escola ID {{ $schoolId }} — {{ count($lista) }} imagem(ns) órfã(s)
                            @endif
                           
                        </span>
                        <div>
                            <input type="checkbox" class="checkAllSchool me-1" data-school="{{ $schoolId }}">
                            <label class="small mb-0">Selecionar todas</label>
                        </div>
                    </div>

                    <div class="card-body p-2 small">
                        <table class="table table-sm align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Selecionar</th>
                                    <th>Pré-visualização</th>
                                    <th>Arquivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lista as $i => $img)
                                    @php
                                        $url = asset('storage/img-user/' . $img);
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><input type="checkbox" name="arquivos[]" value="{{ $img }}"></td>
                                        <td>
                                            <img src="{{ $url }}" alt="Prévia {{ $img }}"
                                                 style="width:70px; height:70px; border-radius:10px; object-fit:cover; cursor:zoom-in;"
                                                 onclick="abrirImagem('{{ $url }}')">
                                        </td>
                                        <td class="text-start">{{ $img }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-danger mt-3"
                    onclick="return confirm('Deseja realmente apagar as imagens selecionadas?')">
                🧹 Remover Selecionadas
            </button>
        </form>
    @endif
</div>

{{-- 🔍 Modal de zoom da imagem --}}
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 text-center">
      <img id="zoomImage" src="" alt="Imagem ampliada" class="img-fluid rounded shadow-lg">
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.checkAllSchool').forEach(chk => {
    chk.addEventListener('change', function(){
        const schoolId = this.dataset.school;
        const table = this.closest('.card');
        table.querySelectorAll('input[name="arquivos[]"]').forEach(cb => cb.checked = this.checked);
    });
});

// zoom da imagem
function abrirImagem(src) {
    const img = document.getElementById('zoomImage');
    img.src = src;
    const modal = new bootstrap.Modal(document.getElementById('modalZoom'));
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
th, td { vertical-align: middle !important; }
.card-header label { cursor: pointer; }
img:hover { opacity: 0.85; transform: scale(1.03); transition: all .2s ease; }
</style>
@endpush




{{--
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>🧹 Limpeza de Imagens Órfãs</h2>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <p class="text-muted">Verifica imagens sem vínculo com alunos existentes.</p>

    <div class="mb-4">
        <strong>Total de imagens válidas:</strong> {{ count($validas) }} <br>
        <strong>Total de órfãs:</strong> {{ count($orfas) }}
    </div>

    @if(count($orfas) > 0)
        <form action="{{ route('master.imagens.limpar') }}" method="POST">
            @csrf
            <div class="alert alert-warning">
                <strong>⚠️ Atenção:</strong> As imagens abaixo não possuem aluno associado. Confirme a exclusão com cuidado.
            </div>

            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>Arquivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orfas as $img)
                        <tr>
                            <td><input type="checkbox" name="arquivos[]" value="{{ $img }}"></td>
                            <td>{{ $img }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Tem certeza que deseja apagar as imagens selecionadas?')">
                🧹 Remover Selecionadas
            </button>
        </form>
    @else
        <div class="alert alert-success">
            ✅ Nenhuma imagem órfã encontrada.
        </div>
    @endif
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', e => {
    document.querySelectorAll('input[name="arquivos[]"]').forEach(cb => cb.checked = e.target.checked);
});
</script>
@endsection
--}}