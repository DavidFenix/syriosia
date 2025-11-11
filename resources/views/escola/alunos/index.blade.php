@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Alunos</h1>
    <a href="{{ route('escola.alunos.create') }}" class="btn btn-primary mb-3">➕ Novo Aluno</a>
    
    <table class="table table-striped align-middle" id="table-alunos">
        <thead>
            <tr>
                <th>#</th>
                <th>Foto</th>
                <th>Matrícula</th>
                <th>Turma</th>
                <th>Nome do Aluno</th>
                <th>Origem</th> {{-- 🏫 Escola de origem --}}
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($alunos as $index => $a)
            @php
                $fotoNome = session('current_school_id').'_'.$a->matricula . '.png';
                $fotoRelPath = 'storage/img-user/' . $fotoNome;
                $fotoAbsoluta = public_path($fotoRelPath);
                $fotoUrl = file_exists($fotoAbsoluta)
                        ? asset($fotoRelPath)
                        : asset('storage/img-user/padrao.png');

                // Busca enturmação mais recente da escola atual
                $enturmacaoAtual = \App\Models\Enturmacao::where('school_id', session('current_school_id'))
                    ->where('aluno_id', $a->id)
                    ->latest()
                    ->with('turma')
                    ->first();
            @endphp

            <tr>
                <td>{{ $index + 1 }}</td>

                {{-- 📷 Foto --}}
                <td class="text-center">
                    <img src="{{ $fotoUrl }}" class="foto-aluno" alt="Foto de {{ $a->nome_a }}"
                         onclick="abrirImagem('{{ $fotoUrl }}')">
                </td>

                {{-- 🆔 Matrícula --}}
                <td>{{ $a->matricula }}</td>

                {{-- 🏫 Turma --}}
                <td>
                    {{ $enturmacaoAtual && $enturmacaoAtual->turma
                        ? $enturmacaoAtual->turma->serie_turma
                        : '—' }}
                </td>

                {{-- 👤 Nome --}}
                <td>{{ $a->nome_a }}</td>

                {{-- 🏫 Origem da Escola --}}
                <td>
                    @if($a->school_id == session('current_school_id'))
                        <span class="badge bg-success">Nativo</span>
                    @else
                        <span class="badge bg-warning text-dark">
                            Vinculado de {{ $a->escola->nome_e ?? 'outra escola' }}
                        </span>
                    @endif
                </td>

                {{-- ⚙️ Ações --}}
                <td class="text-end">
                    <a href="{{ route('escola.alunos.edit', $a) }}"
                       class="btn btn-sm btn-warning me-1" title="Editar aluno">✏️</a>

                    <a href="{{ route('escola.alunos.foto.edit', $a->id) }}" class="btn btn-outline-primary btn-sm">
                        📸 Atualizar Foto
                    </a>

                    <form action="{{ route('escola.alunos.destroy', $a) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Remover este aluno?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" title="Excluir aluno">🗑</button>
                    </form>


                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">Nenhum aluno encontrado</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection



{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Alunos</h1>
    <a href="{{ route('escola.alunos.create') }}" class="btn btn-primary mb-3">➕ Novo Aluno</a>

    <table class="table table-striped" id="table-alunos">
        <thead>
            <tr>
                <th>#</th>
                <th>Foto</th> {{-- 👈 nova coluna -}}
                <th>Matrícula</th>
                <th>Turma</th>
                <th>Nome do Aluno</th>
                <th>Escola Atual</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($alunos as $index => $a)
            @php
                $fotoNome = $a->matricula . '.png';
                $fotoRelPath = 'storage/img-user/' . $fotoNome;
                $fotoAbsoluta = public_path($fotoRelPath);

                // se o arquivo existe, usa ele; senão, cai na imagem padrão
                $fotoUrl = file_exists($fotoAbsoluta)
                        ? asset($fotoRelPath)
                        : asset('storage/img-user/padrao.png');
            @endphp

            <tr>
                {{-- 🔢 Número sequencial -}}
                <td>{{ $index + 1 }}</td>

                <td class="text-center">
                    <img src="{{ $fotoUrl }}" class="foto-aluno" alt="Foto de {{ $a->nome_a }}"
                         onclick="abrirImagem('{{ $fotoUrl }}')">
                </td>

                {{-- 🆔 Matrícula e Nome -}}
                <td>{{ $a->matricula }}</td>
                {{-- 🏫 Turma do aluno -}}
                <td>
                    @php
                        $turma = '—';

                        // só tenta buscar se o relacionamento estiver carregado e não for nulo
                        if ($a->relationLoaded('enturmacao') || method_exists($a, 'enturmacao')) {
                            $primeiraEnturmacao = $a->enturmacao()->with('turma')->first();
                            if ($primeiraEnturmacao && $primeiraEnturmacao->turma) {
                                $turma = $primeiraEnturmacao->turma->serie_turma;
                            }
                        }
                    @endphp
                    {{ $turma }}
                </td>
                <td>{{ $a->nome_a }}</td>

                {{-- Nova coluna: Escola -}}
                <td>{{ optional($a->escola)->nome_e ?? '—' }}</td>

                {{-- ⚙️ Ações -}}
                <td class="text-end">
                    <a href="{{ route('escola.alunos.edit', $a) }}" class="btn btn-sm btn-warning me-1" title="Editar aluno">✏️</a>
                    <form action="{{ route('escola.alunos.destroy', $a) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Remover este aluno?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" title="Excluir aluno">🗑</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">Nenhum aluno encontrado</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
--}}

@push('scripts')
<script>
$(document).ready(function () {
    // inicializa com o script global do public/js/datatables-init.js
    // colunas filtráveis: Nome(1), CPF(2), Escola(3), Roles(4), CNPJ(5)
    const table = initDataTable('#table-alunos', { 
        order: [[3, 'asc'], [4, 'asc']],
        columnDefs: [
            { width: '2%',  targets: 0 }, // #
            { width: '5%',  targets: 1, className: 'text-center' }, // Matrícula
            { width: '5%',  targets: 2, className: 'text-center' }, // Matrícula
            { width: '5%',  targets: 3, className: 'text-center' }, // Nome
            { width: '45%', targets: 4 }, // Turma
            { width: '2%', targets: 5 }, // Turma
            { width: '28%', targets: 6 }, // Ações
            { orderable: false, targets: [6] } // desativa ordenação no # e Ações
        ], 
    }, [2, 3, 4, 5]);

    // 🔹 Atualiza numeração (1, 2, 3...) após ordenação, busca ou paginação
    table.on('order.dt search.dt draw.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
            this.data(i++);
        });
    }).draw();
});

function abrirImagem(src) {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.8)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = 9999;
    overlay.innerHTML = `<img src="${src}" style="max-width:90%; max-height:90%; border-radius:10px;">`;
    overlay.onclick = () => overlay.remove();
    document.body.appendChild(overlay);
}
</script>
@endpush


@push('styles')
<style>
    .foto-aluno {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .foto-aluno:hover {
        transform: scale(1.2);
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    .foto-aluno-sem-imagem {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #eee url('{{ asset("images/default.png") }}') center/cover no-repeat;
        display: inline-block;
    }
</style>
@endpush









{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Alunos</h1>
    <a href="{{ route('escola.alunos.create') }}" class="btn btn-primary mb-3">➕ Novo Aluno</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Matrícula</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alunos as $aluno)
                <tr>
                    <td>{{ $aluno->id }}</td>
                    <td>{{ $aluno->nome_a }}</td>
                    <td>{{ $aluno->matricula }}</td>
                    <td>
                        <a href="{{ route('escola.alunos.edit', $aluno) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('escola.alunos.destroy', $aluno) }}" method="post" style="display:inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir aluno?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Nenhum aluno encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
--}}


{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Alunos</h1>
    <a href="{{ route('escola.alunos.create') }}" class="btn btn-primary mb-3">Novo Aluno</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Matrícula</th>
                <th>Escola</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($alunos as $aluno)
            <tr>
                <td>{{ $aluno->id }}</td>
                <td>{{ $aluno->nome_a }}</td>
                <td>{{ $aluno->matricula }}</td>
                <td>{{ $aluno->escola->nome_e ?? '-' }}</td>
                <td>
                    <a href="{{ route('escola.alunos.edit', $aluno) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('escola.alunos.destroy', $aluno) }}" method="post" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir aluno?')">Excluir</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Nenhum aluno encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
--}}