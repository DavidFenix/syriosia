{{-- DEBUG: custom pagination active --}}

@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h2 class="mb-4">📘 Minhas Ocorrências Registradas</h2>

    {{-- ✅ Mensagens de retorno --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 🧱 Tabela de ocorrências --}}
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0" id="tabela-ocorrencias">
            <thead class="table-light">
                <tr>
                    <th style="width:3%">#</th>
                    <th style="width:5%">Foto</th>
                    <th style="width:11%">Aluno</th>
                    <th style="width:32%">Motivos</th>
                    <th style="width:10%">Professor</th>
                    <th style="width:9%">Disciplina</th>
                    <th style="width:9%">Turma</th>
                    <th style="width:8%">Data</th>
                    <th style="width:7%">Status</th>
                    <th style="width:8%" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ocorrencias as $index => $oc)
                    @php
                        $a = $oc->aluno;
                        $fotoNome = $a->matricula . '.png';
                        $fotoRelPath = 'storage/img-user/' . $fotoNome;
                        $fotoAbsoluta = public_path($fotoRelPath);
                        $fotoUrl = file_exists($fotoAbsoluta)
                            ? asset($fotoRelPath)
                            : asset('storage/img-user/padrao.png');

                        $nome_a = $a->nome_a ?? '';
                        $partes_a = explode(' ', trim($nome_a));
                        $primeiro_a = $partes_a[0] ?? '';
                        $ultimo_a = count($partes_a) > 1 ? end($partes_a) : '';

                        $nome_p = $oc->professor->usuario->nome_u ?? '';
                        $partes_p = explode(' ', trim($nome_p));
                        $primeiro_p = $partes_p[0] ?? '';
                        $ultimo_p = count($partes_p) > 1 ? end($partes_p) : '';

                        $isAutor = $oc->is_autor ?? false;
                        $isDiretor = $oc->is_diretor ?? false;
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-center">
                            <img src="{{ $fotoUrl }}" alt="Foto de {{ $a->nome_a }}" 
                                 class="rounded-circle" width="36" height="36"
                                 style="object-fit: cover; cursor: zoom-in;"
                                 onclick="abrirImagem('{{ $fotoUrl }}')">
                        </td>

                        {{-- 👨‍🎓 Aluno --}}
                        <td class="fw-semibold">
                            {{ $a->nome_a }}
                            {{-- ⚠️ Durante testes, manter nome completo.
                                 Quando tudo estiver validado, trocar para: {{ $primeiro_a.' '.$ultimo_a }} --}}
                            <br>
                            <small class="text-muted" style="font-size:0.75rem;">
                                {{ $a->matricula }}
                            </small>
                        </td>


                        {{-- 🧾 Motivos resumidos --}}
                        <td style="white-space: normal;">
                            @php
                                $descricao = trim($oc->descricao ?? '');
                                $motivos = $oc->motivos->pluck('descricao')->toArray();
                                $todos = array_filter(array_merge([$descricao], $motivos));
                                $textoFinal = implode(' / ', $todos);
                                $textoCurto = Str::limit($textoFinal, 120);
                            @endphp
                            @if(!empty($textoFinal))
                                <span class="small">{{ $textoFinal }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- 👨‍🏫 Professor --}}
                        <td>{{ $primeiro_p.' '.$ultimo_p }}</td>

                        {{-- 📘 Disciplina / Turma / Data / Status --}}
                        <td>{{ $oc->oferta->disciplina->abr ?? '—' }}</td>
                        <td>{{ $oc->oferta->turma->serie_turma ?? '—' }}</td>
                        <td class="text-center">{{ $oc->created_at->format('d/m/Y') }}</td>

                        <td>
                            @if($oc->status == 1)
                                <span class="badge bg-success">Ativa</span>
                            @elseif($oc->status == 0)
                                <span class="badge bg-secondary">Arquivada</span>
                            @elseif($oc->status == 2)
                                <span class="badge bg-danger">Anulada</span>
                            @endif
                        </td>

                        {{-- ⚙️ Ações --}}
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('professor.ocorrencias.show', $oc->id) }}"
                                   class="btn btn-outline-primary btn-sm">🔍</a>
                                @if($isAutor)
                                    <a href="{{ route('professor.ocorrencias.edit', $oc->id) }}"
                                       class="btn btn-outline-warning btn-sm">✏️</a>
                                    <form action="{{ route('professor.ocorrencias.destroy', $oc->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('Excluir esta ocorrência?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">🗑</button>
                                    </form>
                                @endif
                                @if($isDiretor)
                                    <a href="{{ route('professor.ocorrencias.encaminhar', $oc->id) }}"
                                       class="btn btn-outline-success btn-sm">📁</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Nenhuma ocorrência registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔘 Controle de visualização --}}
    <div class="d-flex justify-content-between align-items-right mt-3">
        
        {{-- Paginação Laravel (só aparece quando $ocorrencias é paginada) --}}
        @php
            $isPaginated = $ocorrencias instanceof \Illuminate\Pagination\LengthAwarePaginator
                        || $ocorrencias instanceof \Illuminate\Pagination\Paginator;
        @endphp


        @if($isPaginated && $ocorrencias->total() > $ocorrencias->perPage())
            <div class="pagination-container mb-0">
                {{ $ocorrencias->links() }}
            </div>
        @endif

         @if(!$isPaginated)
            <div class="alert alert-info py-2 small mb-2">
                Exibindo todas as ocorrências (modo “Ver tudo”).
            </div>
        @endif


        <form method="GET" id="formVerTudo">
            <input type="hidden" name="perPage" id="perPageInput" value="{{ request('perPage', 25) }}">
            <button type="button" id="toggleVerTudo" class="btn btn-sm btn-outline-secondary">
                {{ request('perPage', 25) > 25 ? '🔙 Paginar por 25' : '👁️ Ver tudo' }}
            </button>
        </form>
       
    </div>

</div>

{{-- 🔍 Modal zoom imagem --}}
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 text-center">
      <img id="zoomImage" src="" alt="Foto ampliada" class="img-fluid rounded shadow-lg">
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
    #tabela-ocorrencias th, #tabela-ocorrencias td {
        vertical-align: middle !important;
        white-space: nowrap;
        font-size: 0.88rem;
    }
    #tabela-ocorrencias td:nth-child(4) {
        white-space: normal !important;
        word-break: break-word;
    }
    #tabela-ocorrencias td:nth-child(3) {
        white-space: normal !important;
        word-break: break-word;
    }
    #tabela-ocorrencias td:nth-child(5) {
        /*max-width: 140px;*/
        /*overflow: hidden;*/
        /*text-overflow: ellipsis;*/
    }
    #tabela-ocorrencias td:nth-child(6) {
        white-space: normal !important;
        word-break: break-word;
    }
    #tabela-ocorrencias td:nth-child(7) {
        /*max-width: 90px;*/
        /*overflow: hidden;*/
        /*text-overflow: ellipsis;*/
    }
    #tabela-ocorrencias td:nth-child(8) {
        text-align: center;
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script>
function abrirImagem(src) {
    const img = document.getElementById('zoomImage');
    img.src = src;
    const modal = new bootstrap.Modal(document.getElementById('modalZoom'));
    modal.show();
}

$(document).ready(function () {
    const table = initDataTable('#tabela-ocorrencias', { 
        order: [[6, 'asc'], [2, 'asc']],
        pageLength: 25,
        columnDefs: [
            { width: '1%',  targets: 0 },
            { width: '5%',  targets: 1, className: 'text-center' },
            { width: '10%',  targets: 2, className: 'text-center' },
            { width: '40%',  targets: 3, className: 'text-center' },
            { width: '10%', targets: 4, className: 'text-center' },
            { width: '5%', targets: 5, className: 'text-center' },
            { width: '5%', targets: 6, className: 'text-center' },
            { width: '10%', targets: 7, className: 'text-center' },
            { width: '1%', targets: 8, className: 'text-center' },
            { width: '10%', targets: 9, className: 'text-center' },
            { orderable: false, targets: [1,9] }
        ], 
    }, [2, 3, 4, 5, 6, 7, 8]);

    // 🔹 Atualiza numeração após ordenação ou busca
    table.on('order.dt search.dt draw.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

    // 🔘 Alternância entre ver tudo / paginar
    $('#toggleVerTudo').on('click', function() {
        const current = parseInt($('#perPageInput').val());
        $('#perPageInput').val(current > 25 ? 25 : 9999);
        $('#formVerTudo').submit();
    });
});
</script>
@endpush








{{--
@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h2 class="mb-4">📘 Minhas Ocorrências Registradas</h2>

    {{-- ✅ Mensagens de retorno -}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 🧱 Tabela de ocorrências -}}
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0" id="tabela-ocorrencias">
            <thead class="table-light">
                <tr>
                    <th style="width:3%">#</th>
                    <th style="width:5%">Foto</th>
                    <th style="width:11%">Aluno</th>
                    <th style="width:32%">Motivos</th>
                    <th style="width:10%">Professor</th>
                    <th style="width:9%">Disciplina</th>
                    <th style="width:9%">Turma</th>
                    <th style="width:8%">Data</th>
                    <th style="width:7%">Status</th>
                    <th style="width:8%" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>

                @forelse($ocorrencias as $index => $oc)

                    @php
                        $a = $oc->aluno;
                        $fotoNome = $a->matricula . '.png';
                        $fotoRelPath = 'storage/img-user/' . $fotoNome;
                        $fotoAbsoluta = public_path($fotoRelPath);
                        $fotoUrl = file_exists($fotoAbsoluta)
                            ? asset($fotoRelPath)
                            : asset('storage/img-user/padrao.png');

                        $nome_a = $a->nome_a ?? '';
                        $partes_a = explode(' ', trim($nome_a));
                        $primeiro_a = $partes_a[0] ?? '';
                        $ultimo_a = count($partes_a) > 1 ? end($partes_a) : '';

                        $nome_p = $oc->professor->usuario->nome_u ?? '';
                        $partes_p = explode(' ', trim($nome_p));
                        $primeiro_p = $partes_p[0] ?? '';
                        $ultimo_p = count($partes_p) > 1 ? end($partes_p) : '';

                        $isAutor = $oc->is_autor ?? false;
                        $isDiretor = $oc->is_diretor ?? false;
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        {{-- 📸 Foto -}}
                        <td class="text-center">
                            <img src="{{ $fotoUrl }}" alt="Foto de {{ $a->nome_a }}" 
                                 class="rounded-circle" width="36" height="36"
                                 style="object-fit: cover; cursor: zoom-in;"
                                 onclick="abrirImagem('{{ $fotoUrl }}')">
                        </td>

                        {{-- 👨‍🎓 Aluno -}}
                        <td class="fw-semibold">
                            {{ $primeiro_a.' '.$ultimo_a }}
                            <br>
                            <small class="text-muted" style="font-size:0.75rem;">
                                {{ $a->matricula }}
                            </small>
                        </td>

                        {{-- 🧾 Motivos -}}
                        <td style="white-space: normal;">
                            @php
                                // Junta a descrição principal + todos os motivos em uma única string
                                $descricao = trim($oc->descricao ?? '');
                                $motivos = $oc->motivos->pluck('descricao')->toArray();
                                $todos = array_filter(array_merge([$descricao], $motivos)); // remove vazios
                                $textoFinal = implode(' / ', $todos);
                                $textoCurto = Str::limit($textoFinal, 120);
                            @endphp

                            @if(!empty($textoFinal))
                                <span class="small">{{ $textoCurto }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>


                        {{------------------------- 🧾 Motivos -}}
                        <td style="white-space: normal;">
                            @if($oc->motivos->count() > 0)
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($oc->motivos->take(3) as $m)
                                        <li>• {{ Str::limit($m->descricao, 60) }}</li>
                                    @endforeach
                                    @if($oc->motivos->count() > 3)
                                        <li class="text-muted">+{{ $oc->motivos->count() - 3 }} outros</li>
                                    @endif
                                </ul>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        -----------------------------------------}}

                        {{-- 👨‍🏫 Professor -}}
                        <td>{{ $primeiro_p.' '.$ultimo_p }}</td>

                        {{-- 📘 Disciplina / Turma / Data / Status -}}
                        <td>{{ $oc->oferta->disciplina->abr ?? '—' }}</td>
                        <td>{{ $oc->oferta->turma->serie_turma ?? '—' }}</td>
                        <td class="text-center">{{ $oc->created_at->format('d/m/Y') }}</td>

                        <td>
                            @if($oc->status == 1)
                                <span class="badge bg-success">Ativa</span>
                            @elseif($oc->status == 0)
                                <span class="badge bg-secondary">Arquivada</span>
                            @elseif($oc->status == 2)
                                <span class="badge bg-danger">Anulada</span>
                            @endif
                        </td>

                        {{-- ⚙️ Ações -}}
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                {{-- 🔍 Ver -}}
                                <a href="{{ route('professor.ocorrencias.show', $oc->id) }}"
                                   class="btn btn-outline-primary btn-sm">🔍</a>

                                {{-- ✏️ Editar -}}
                                @if($isAutor)
                                    <a href="{{ route('professor.ocorrencias.edit', $oc->id) }}"
                                       class="btn btn-outline-warning btn-sm">✏️</a>
                                @endif

                                {{-- 🗑 Excluir -}}
                                @if($isAutor)
                                    <form action="{{ route('professor.ocorrencias.destroy', $oc->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('Excluir esta ocorrência?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">🗑</button>
                                    </form>
                                @endif

                                {{-- 📁 Encaminhar -}}
                                @if($isDiretor)
                                    <a href="{{ route('professor.ocorrencias.encaminhar', $oc->id) }}"
                                       class="btn btn-outline-success btn-sm">📁</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Nenhuma ocorrência registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $ocorrencias->links() }}
</div>


{{-- 🔍 Modal para zoom da imagem -}}
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 text-center">
      <img id="zoomImage" src="" alt="Foto ampliada" class="img-fluid rounded shadow-lg">
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
    #tabela-ocorrencias th, #tabela-ocorrencias td {
        vertical-align: middle !important;
        white-space: nowrap;
        font-size: 0.88rem;
    }

    /* Permite que apenas "Motivos" quebre linha */
    #tabela-ocorrencias td:nth-child(4) {
        white-space: normal !important;
        word-break: break-word;
    }

    /* Compacta colunas de nome e professor */
    #tabela-ocorrencias td:nth-child(3),
    #tabela-ocorrencias td:nth-child(5) {
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Compacta Disciplina e Turma */
    #tabela-ocorrencias td:nth-child(6),
    #tabela-ocorrencias td:nth-child(7) {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #tabela-ocorrencias td:nth-child(8) {
        text-align: center;
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script>
function abrirImagem(src) {
    const img = document.getElementById('zoomImage');
    img.src = src;
    const modal = new bootstrap.Modal(document.getElementById('modalZoom'));
    modal.show();
}

$(document).ready(function () {
    // inicializa com o script global do public/js/datatables-init.js
    // colunas filtráveis: Nome(1), CPF(2), Escola(3), Roles(4), CNPJ(5)
    const table = initDataTable('#tabela-ocorrencias', { 
        order: [[6, 'asc'], [2, 'asc']],
        columnDefs: [
            { width: '1%',  targets: 0 }, // #
            { width: '5%',  targets: 1, className: 'text-center' }, // Matrícula
            { width: '10%',  targets: 2, className: 'text-center' }, // Matrícula
            { width: '40%',  targets: 3, className: 'text-center' }, // Nome
            { width: '10%', targets: 4, className: 'text-center' }, 
            { width: '5%', targets: 5, className: 'text-center' }, 
            { width: '5%', targets: 6, className: 'text-center' }, 
            { width: '10%', targets: 7, className: 'text-center' }, 
            { width: '1%', targets: 8, className: 'text-center' }, 
            { width: '10%', targets: 9, className: 'text-center' }, 
            { orderable: false, targets: [1,9] } // desativa ordenação no # e Ações
        ], 
    }, [2, 3, 4, 5, 6, 7, 8]);

    // 🔹 Atualiza numeração (1, 2, 3...) após ordenação, busca ou paginação
    table.on('order.dt search.dt draw.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();

});

console.log('Total renderizado pelo Laravel:', {{ count($ocorrencias) }});

</script>

@endpush
--}}





{{--
@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h2 class="mb-4">📘 Minhas Ocorrências Registradas</h2>

    {{-- ✅ Mensagens de retorno -}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 🧱 Tabela de ocorrências -}}
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0" id="tabela-ocorrencias">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Aluno</th>
                    <th>Motivos</th>
                    <th>Professor</th>
                    <th>Disciplina</th>
                    <th>Turma</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ocorrencias as $index => $oc)

                    @php
                        $a = $oc->aluno;
                        $fotoNome = $a->matricula . '.png';
                        $fotoRelPath = 'storage/img-user/' . $fotoNome;
                        $fotoAbsoluta = public_path($fotoRelPath);
                        $fotoUrl = file_exists($fotoAbsoluta)
                            ? asset($fotoRelPath)
                            : asset('storage/img-user/padrao.png');

                        $nome_a = $a->nome_a ?? '';
                        $partes_a = explode(' ', trim($nome_a));
                        $primeiro_a = $partes_a[0] ?? '';
                        $ultimo_a = count($partes_a) > 1 ? end($partes_a) : '';

                        $nome_p = $oc->professor->usuario->nome_u ?? '';
                        $partes_p = explode(' ', trim($nome_p));
                        $primeiro_p = $partes_p[0] ?? '';
                        $ultimo_p = count($partes_p) > 1 ? end($partes_p) : '';

                        // Permissões (agora vindas diretamente do controller)
                        $isAutor = $oc->is_autor ?? false;
                        $isDiretor = $oc->is_diretor ?? false;
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td class="text-center">
                            <img src="{{ $fotoUrl }}" alt="Foto de {{ $a->nome_a }}" 
                                 class="rounded-circle" width="45" height="45"
                                 style="object-fit: cover; cursor: zoom-in;"
                                 onclick="abrirImagem('{{ $fotoUrl }}')">
                        </td>

                        <td class="fw-semibold">
                            {{ $primeiro_a.' '.$ultimo_a }}
                            <br>
                            <small class="text-muted">{{ $a->matricula }}</small>
                        </td>

                        {{-- Motivos resumidos -}}
                        <td>
                            @if($oc->motivos->count() > 0)
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($oc->motivos->take(2) as $m)
                                        <li>• {{ Str::limit($m->descricao, 30) }}</li>
                                    @endforeach
                                    @if($oc->motivos->count() > 2)
                                        <li class="text-muted">+{{ $oc->motivos->count() - 2 }} outros</li>
                                    @endif
                                </ul>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>{{ $primeiro_p.' '.$ultimo_p }}</td>
                        <td>{{ $oc->oferta->disciplina->abr ?? '—' }}</td>
                        <td>{{ $oc->oferta->turma->serie_turma ?? '—' }}</td>
                        <td>{{ $oc->created_at->format('d/m/Y H:i') }}</td>

                        {{-- Status visual -}}
                        <td>
                            @if($oc->status == 1)
                                <span class="badge bg-success">Ativa</span>
                            @elseif($oc->status == 0)
                                <span class="badge bg-secondary">Arquivada</span>
                            @elseif($oc->status == 2)
                                <span class="badge bg-danger">Anulada</span>
                            @endif
                        </td>

                        {{-- Ações -}}
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                {{-- 🔍 Ver -}}
                                <a href="{{ route('professor.ocorrencias.show', $oc->id) }}"
                                   class="btn btn-outline-primary btn-sm">🔍 Ver</a>

                                {{-- ✏️ Editar (apenas autor) -}}
                                @if($isAutor)
                                    <a href="{{ route('professor.ocorrencias.edit', $oc->id) }}"
                                       class="btn btn-outline-warning btn-sm">✏️ Editar</a>
                                @endif

                                {{-- 🗑 Excluir (apenas autor) -}}
                                @if($isAutor)
                                    <form action="{{ route('professor.ocorrencias.destroy', $oc->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('Excluir esta ocorrência?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">🗑 Excluir</button>
                                    </form>
                                @endif

                                {{-- 📁 Encaminhar / Arquivar (somente diretor) -}}
                                @if($isDiretor)
                                    <a href="{{ route('professor.ocorrencias.encaminhar', $oc->id) }}"
                                       class="btn btn-outline-success btn-sm">📁 Encaminhar</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Nenhuma ocorrência registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- 🔍 Modal zoom imagem -}}
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 text-center">
      <img id="zoomImage" src="" alt="Foto ampliada" class="img-fluid rounded shadow-lg">
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
th, td { vertical-align: middle !important; }
ul.small li { line-height: 1.3; }
</style>
@endpush

@push('scripts')
<script>
function abrirImagem(src) {
    const img = document.getElementById('zoomImage');
    img.src = src;
    const modal = new bootstrap.Modal(document.getElementById('modalZoom'));
    modal.show();
}

$(document).ready(function () {
    initDataTable('#tabela-ocorrencias', { 
        autoWidth: false,
        columnDefs: [
            { width: "1%", targets: 0 },
            { width: "6%", targets: 1 },
            { width: "12%", targets: 2 },
            { width: "30%", targets: 3 },
            { width: "10%", targets: 4 },
            { width: "10%", targets: 5 },
            { width: "12%", targets: 6 },
            { width: "10%", targets: 7 },
            { width: "7%", targets: 8 },
            { width: "12%", targets: 9 },
        ],
        order: [[4, 'asc'],[1, 'asc']] }, [2, 3, 4, 5, 6, 7, 8]);
});
</script>
@endpush
--}}



{{--
@extends('layouts.app')

@section('content')
<div class="container py-3">

    <h2 class="mb-4">📘 Minhas Ocorrências Registradas</h2>

    {{-- ✅ Mensagens de retorno -}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 🔍 Filtros de pesquisa -}}
    <form method="GET" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="turma" class="form-label fw-semibold">Turma</label>
                <input type="text" name="turma" id="turma" value="{{ request('turma') }}" class="form-control" placeholder="Ex: 2ª Série A">
            </div>
            <div class="col-md-3">
                <label for="disciplina" class="form-label fw-semibold">Disciplina</label>
                <input type="text" name="disciplina" id="disciplina" value="{{ request('disciplina') }}" class="form-control" placeholder="Ex: Matemática">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ativas</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Arquivadas</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Anuladas</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-outline-primary">🔍 Filtrar</button>
                <a href="{{ route('professor.ocorrencias.index') }}" class="btn btn-outline-secondary">🔄 Limpar</a>
            </div>
        </div>
    </form>-}}

    {{-- 🧱 Tabela de ocorrências -}}
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle mb-0" id="tabela-ocorrencias">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Aluno</th>
                    <th>Motivos</th>
                    <th>Professor</th>
                    <th>Disciplina</th>
                    <th>Turma</th>
                    <th>Data</th>
                    <th>Status</th>
                    
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ocorrencias as $index => $oc)

                    @php
                        $a = $oc->aluno;
                        $fotoNome = $a->matricula . '.png';
                        $fotoRelPath = 'storage/img-user/' . $fotoNome;
                        $fotoAbsoluta = public_path($fotoRelPath);
                        $fotoUrl = file_exists($fotoAbsoluta)
                            ? asset($fotoRelPath)
                            : asset('storage/img-user/padrao.png');

                        $nome_a = $a->nome_a ?? '';
                        $partes_a = explode(' ', trim($nome_a));
                        $primeiro_a = $partes_a[0] ?? '';
                        $ultimo_a = count($partes_a) > 1 ? end($partes_a) : '';

                        $nome_p = $oc->professor->usuario->nome_u ?? '';
                        $partes_p = explode(' ', trim($nome_p));
                        $primeiro_p = $partes_p[0] ?? '';
                        $ultimo_p = count($partes_p) > 1 ? end($partes_p) : '';

                    @endphp

                    <tr>
                        <td>{{ $index + $ocorrencias->firstItem() }}</td>

                        <td class="text-center">
                            <img src="{{ $fotoUrl }}" alt="Foto de {{ $a->nome_a }}" 
                                 class="rounded-circle" width="45" height="45"
                                 style="object-fit: cover; cursor: zoom-in;"
                                 onclick="abrirImagem('{{ $fotoUrl }}')">
                        </td>

                        <td class="fw-semibold">
                            {{ $primeiro_a.' '.$ultimo_a }}
                            <br>
                            <small class="text-muted">{{ $oc->aluno->matricula ?? '' }}</small>
                        </td>

                        {{-- Lista resumida dos motivos -}}
                        <td>
                            @if($oc->motivos->count() > 0)
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($oc->motivos->take(2) as $m)
                                        <li>• {{ Str::limit($m->descricao, 30) }}</li>
                                    @endforeach
                                    @if($oc->motivos->count() > 2)
                                        <li class="text-muted">+{{ $oc->motivos->count() - 2 }} outros</li>
                                    @endif
                                </ul>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>{{ $primeiro_p.' '.$ultimo_p }}</td>

                        <td>{{ $oc->oferta->disciplina->abr ?? '—' }}</td>
                        <td>{{ $oc->oferta->turma->serie_turma ?? '—' }}</td>
                        <td>{{ $oc->created_at->format('d/m/Y H:i') }}</td>

                        {{-- Status visual com cores -}}
                        <td>
                            @if($oc->status == 1)
                                <span class="badge bg-success">Ativa</span>
                            @elseif($oc->status == 0)
                                <span class="badge bg-secondary">Arquivada</span>
                            @elseif($oc->status == 2)
                                <span class="badge bg-danger">Anulada</span>
                            @endif
                        </td>

                        

                        {{-- ⚙️ Ações -}}
                        <td class="text-end">
                            <div class="btn-group mt-3" role="group">
                                {{-- 🔍 Ver -}}
                                <a href="{{ route('professor.ocorrencias.show', $oc->id) }}" class="btn btn-outline-primary btn-sm">🔍 Ver</a>

                                {{-- ✏️ Editar -}}
                                @if($oc->is_autor)
                                    <a href="{{ route('professor.ocorrencias.edit', $oc->id) }}" class="btn btn-outline-warning btn-sm">✏️ Editar</a>
                                @endif

                                {{-- 🗑 Excluir -}}
                                @if($oc->is_autor)
                                    <form action="{{ route('professor.ocorrencias.destroy', $oc->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Deseja excluir esta ocorrência?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">🗑 Excluir</button>
                                    </form>
                                @endif

                                {{-- 📁 Arquivar / Encaminhar -}}
                                @if($oc->is_diretor)
                                    <a href="{{ route('professor.ocorrencias.encaminhar', $oc->id) }}" class="btn btn-outline-success btn-sm">
                                        📁 Encaminhar / Arquivar
                                    </a>
                                @endif

                            </div>

                            {{--
                            @if($oc->status == 1)
                                <form action="{{ route('professor.ocorrencias.updateStatus', $oc->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="0">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        🗃 Arquivar
                                    </button>
                                </form>

                                <form action="{{ route('professor.ocorrencias.updateStatus', $oc->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        ❌ Anular
                                    </button>
                                </form>
                            @endif
                            -}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Nenhuma ocorrência registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📄 Paginação -}}
    <div class="mt-3">
        {{ $ocorrencias->links() }}
    </div>

</div>

{{-- 🔍 Modal para zoom da imagem -}}
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0 text-center">
      <img id="zoomImage" src="" alt="Foto ampliada" class="img-fluid rounded shadow-lg">
    </div>
  </div>
</div>

@endsection

@push('styles')
{{-- 💅 CSS adicional -}}
<style>
    th, td {
        vertical-align: middle !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    initDataTable('#tabela-ocorrencias', { 
        autoWidth: false,
        columnDefs: [
            { width: "3%", targets: 0 },
            { width: "6%", targets: 1 },
            { width: "12%", targets: 2 },
            { width: "30%", targets: 3 },
            { width: "10%", targets: 4 },
            { width: "10%", targets: 5 },
            { width: "12%", targets: 6 },
            { width: "10%", targets: 7 },
            { width: "7%", targets: 8 },
            { width: "12%", targets: 9 },
        ],
        order: [[4, 'asc'],[1, 'asc']] }, [2, 3, 4, 5, 6, 7, 8]);
});

function abrirImagem(src) {
    const img = document.getElementById('zoomImage');
    img.src = src;
    const modal = new bootstrap.Modal(document.getElementById('modalZoom'));
    modal.show();
}
</script>
@endpush
--}}