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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ dashboard_route() }}">⚡ Syrios</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMaster"
                aria-controls="navbarMaster" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMaster">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                {{-- ========================================================= --}}
                {{-- 🧩 MASTER --}}
                {{-- ========================================================= --}}
                @if(session('current_role') === 'master')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuMaster" role="button" data-bs-toggle="dropdown">
                            ⚙️ Administração
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('master.escolas.index') }}">🏫 Escolas</a></li>
                            <li><a class="dropdown-item" href="{{ route('master.roles.index') }}">🔑 Roles</a></li>
                            <li><a class="dropdown-item" href="{{ route('master.usuarios.index') }}">👥 Usuários</a></li>
                            <li><a class="dropdown-item" href="{{ route('master.escolas.associacoes') }}">🔗 Associações</a></li>
                            <li><a class="dropdown-item" href="{{ route('master.imagens.index') }}">🧹 Limpeza de Imagens</a></li>
                        </ul>
                    </li>
                @endif


                {{-- ========================================================= --}}
                {{-- 🏛️ SECRETARIA --}}
                {{-- ========================================================= --}}
                @if(session('current_role') === 'secretaria')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuSecretaria" role="button" data-bs-toggle="dropdown">
                            🏛️ Secretaria
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('secretaria.escolas.index') }}">🏫 Escolas Filhas</a></li>
                            <li><a class="dropdown-item" href="{{ route('secretaria.usuarios.index') }}">👥 Usuários</a></li>
                        </ul>
                    </li>
                @endif


                {{-- ========================================================= --}}
                {{-- 🏫 ESCOLA --}}
                {{-- ========================================================= --}}
                @if(session('current_role') === 'escola')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuEscolaPessoas" role="button" data-bs-toggle="dropdown">
                            👨‍🏫 Pessoas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('escola.professores.index') }}">Professores</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.alunos.index') }}">Alunos</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuEscolaAcad" role="button" data-bs-toggle="dropdown">
                            📚 Acadêmico
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('escola.disciplinas.index') }}">Disciplinas</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.turmas.index') }}">Turmas</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.enturmacao.index') }}">Enturmação</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.lotacao.index') }}">Lotação</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuEscolaConfig" role="button" data-bs-toggle="dropdown">
                            ⚙️ Configurações
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('escola.motivos.index') }}">🧩 Motivos de Ocorrência</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.alunos.fotos.lote') }}">📦 Upload em Massa de Fotos</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.regimento.index') }}">📜 Regimento Escolar</a></li>
                            <li><a class="dropdown-item" href="{{ route('escola.identidade.edit') }}">🏫 Identidade Escolar</a></li>
                        </ul>
                    </li>
                @endif


                {{-- ========================================================= --}}
                {{-- 👨‍🏫 PROFESSOR --}}
                {{-- ========================================================= --}}
                @if(session('current_role') === 'professor')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuProfessor" role="button" data-bs-toggle="dropdown">
                            👨‍🏫 Professor
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('professor.ofertas.index') }}">📚 Minhas Ofertas</a></li>
                            <li><a class="dropdown-item" href="{{ route('professor.ocorrencias.index') }}">⚠️ Ocorrências</a></li>
                            <li><a class="dropdown-item" href="{{ route('regimento.visualizar', session('current_school_id')) }}">📜 Regimento Escolar</a></li>
                        </ul>
                    </li>
                @endif

            </ul>

            {{-- ========================================================= --}}
            {{-- 🎯 CONTEXTO + USUÁRIO + LOGOUT --}}
            {{-- ========================================================= --}}
            <ul class="navbar-nav ms-auto">
                @auth
                    {{-- Contexto atual --}}
                    @if(session('current_role') && session('current_school_id'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                                🎯 {{ ucfirst(session('current_role')) }}
                                @php
                                    $escolaAtual = \App\Models\Escola::find(session('current_school_id'));
                                @endphp
                                @if($escolaAtual)
                                    — {{ $escolaAtual->nome_e }}
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('choose.school') }}">🔄 Trocar de contexto</a></li>
                            </ul>
                        </li>
                    @endif

                    @php
                        $nome = Auth::user()->nome_u ?? '';
                        $partes = explode(' ', trim($nome));
                        $primeiro = $partes[0] ?? '';
                        $ultimo = count($partes) > 1 ? end($partes) : '';
                    @endphp

                    <li class="nav-item"><span class="nav-link">👤 {{ $primeiro.' '.$ultimo }}</span></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-link nav-link">🚪 Sair</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{--desativado
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ dashboard_route() }}">⚡ Syrios</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMaster"
                aria-controls="navbarMaster" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMaster">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                {{-- ========================================================= -}}
                {{-- 🧩 MASTER -}}
                {{-- ========================================================= -}}
                @if(session('current_role') === 'master')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/escolas*') ? 'active' : '' }}"
                           href="{{ route('master.escolas.index') }}">
                            🏫 Escolas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/roles*') ? 'active' : '' }}"
                           href="{{ route('master.roles.index') }}">
                            ⚙️ Roles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/usuarios*') ? 'active' : '' }}"
                           href="{{ route('master.usuarios.index') }}">
                            👥 Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/escolas-associacoes*') ? 'active' : '' }}"
                           href="{{ route('master.escolas.associacoes') }}">
                            🔗 Associações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('master.imagens.index') }}" 
                           class="nav-link {{ request()->is('master/imagens*') ? 'active' : '' }}">
                           🧹 Limpeza de Imagens Órfãs
                        </a>
                    </li>
                @endif


                {{-- ========================================================= -}}
                {{-- 🏛️ SECRETARIA -}}
                {{-- ========================================================= -}}
                @if(session('current_role') === 'secretaria')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('secretaria/escolas*') ? 'active' : '' }}"
                           href="{{ route('secretaria.escolas.index') }}">
                            🏫 Escolas Filhas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('secretaria/usuarios*') ? 'active' : '' }}"
                           href="{{ route('secretaria.usuarios.index') }}">
                            👥 Usuários
                        </a>
                    </li>
                @endif


                {{-- ========================================================= -}}
                {{-- 🏫 ESCOLA -}}
                {{-- ========================================================= -}}
                @if(session('current_role') === 'escola')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/professores*') ? 'active' : '' }}"
                           href="{{ route('escola.professores.index') }}">
                            👨‍🏫 Professores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/alunos*') ? 'active' : '' }}"
                           href="{{ route('escola.alunos.index') }}">
                            🎓 Alunos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/disciplinas*') ? 'active' : '' }}"
                           href="{{ route('escola.disciplinas.index') }}">
                            📚 Disciplinas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/turmas*') ? 'active' : '' }}"
                           href="{{ route('escola.turmas.index') }}">
                            🏷️ Turmas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/enturmacao*') ? 'active' : '' }}"
                           href="{{ route('escola.enturmacao.index') }}">
                            🧮 Enturmação
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/lotacao*') ? 'active' : '' }}"
                           href="{{ route('escola.lotacao.index') }}">
                            🧑‍🏫 Lotação
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/motivos*') ? 'active' : '' }}"
                           href="{{ route('escola.motivos.index') }}">
                            🧩 Motivos de Ocorrência
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/alunos/fotos-lote') ? 'active' : '' }}"
                           href="{{ route('escola.alunos.fotos.lote') }}">
                            📦 Upload em Massa de Fotos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/regimento*') ? 'active' : '' }}"
                           href="{{ route('escola.regimento.index') }}">
                            📜 Regimento Escolar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/identidade*') ? 'active' : '' }}"
                           href="{{ route('escola.identidade.edit') }}">
                            🏫 Identidade da Escola
                        </a>
                    </li>
                @endif


                {{-- ========================================================= -}}
                {{-- 👨‍🏫 PROFESSOR -}}
                {{-- ========================================================= -}}
                @if(session('current_role') === 'professor')
                    <!--li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/dashboard*') ? 'active' : '' }}"
                           href="{{ route('professor.dashboard') }}">
                            🏠 Painel
                        </a>
                    </li-->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/ofertas*') ? 'active' : '' }}"
                           href="{{ route('professor.ofertas.index') }}">
                            📚 Minhas Ofertas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/ocorrencias*') ? 'active' : '' }}"
                           href="{{ route('professor.ocorrencias.index') }}">
                            ⚠️ Ocorrências
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('regimento.visualizar', session('current_school_id')) }}">
                            📜 Regimento Escolar
                        </a>
                    </li>

                    <!--li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/relatorios*') ? 'active' : '' }}"
                           href="{{ route('professor.relatorios.index') }}">
                            📊 Relatórios
                        </a>
                    </li-->
                    <!--li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/perfil*') ? 'active' : '' }}"
                           href="{{ route('professor.perfil') }}">
                            👤 Meu Perfil
                        </a>
                    </li-->
                @endif
            </ul>


            {{-- ========================================================= -}}
            {{-- 🎯 CONTEXTO ATUAL + USUÁRIO + LOGOUT -}}
            {{-- ========================================================= -}}
            <ul class="navbar-nav ms-auto">
                @auth
                    {{-- Contexto atual -}}
                    @if(session('current_role') && session('current_school_id'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                                🎯 {{ ucfirst(session('current_role')) }}
                                @php
                                    $escolaAtual = \App\Models\Escola::find(session('current_school_id'));
                                @endphp
                                @if($escolaAtual)
                                    — {{ $escolaAtual->nome_e }}
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('choose.school') }}">
                                        🔄 Trocar de contexto
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @php
        
                        $nome = Auth::user()->nome_u ?? '';
                        $partes = explode(' ', trim($nome));
                        $primeiro = $partes[0] ?? '';
                        $ultimo = count($partes) > 1 ? end($partes) : '';
 
                    @endphp

                    <li class="nav-item">
                        <span class="nav-link">👤 {{ $primeiro.' '.$ultimo }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-link nav-link">🚪 Sair</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                @endauth
            </ul>
        </div>


        {{--desativado
        <div class="collapse navbar-collapse" id="navbarMaster">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                {{-- MASTER -}}
                @if(session('current_role') === 'master')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/escolas*') ? 'active' : '' }}"
                           href="{{ route('master.escolas.index') }}">
                            🏫 Escolas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/roles*') ? 'active' : '' }}"
                           href="{{ route('master.roles.index') }}">
                            ⚙️ Roles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/usuarios*') ? 'active' : '' }}"
                           href="{{ route('master.usuarios.index') }}">
                            👥 Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master/escolas-associacoes*') ? 'active' : '' }}"
                           href="{{ route('master.escolas.associacoes') }}">
                            🔗 Associações
                        </a>
                    </li>
                @endif

                {{-- SECRETARIA -}}
                @if(session('current_role') === 'secretaria')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('secretaria/escolas*') ? 'active' : '' }}"
                           href="{{ route('secretaria.escolas.index') }}">
                            🏫 Escolas Filhas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('secretaria/usuarios*') ? 'active' : '' }}"
                           href="{{ route('secretaria.usuarios.index') }}">
                            👥 Usuários
                        </a>
                    </li>
                @endif

                {{-- ESCOLA -}}
                @if(session('current_role') === 'escola')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/professores*') ? 'active' : '' }}"
                           href="{{ route('escola.professores.index') }}">
                            👨‍🏫 Professores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/alunos*') ? 'active' : '' }}"
                           href="{{ route('escola.alunos.index') }}">
                            🎓 Alunos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/disciplinas*') ? 'active' : '' }}"
                           href="{{ route('escola.disciplinas.index') }}">
                            📚 Disciplinas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/turmas*') ? 'active' : '' }}"
                           href="{{ route('escola.turmas.index') }}">
                            🏷️ Turmas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/enturmacao*') ? 'active' : '' }}"
                           href="{{ route('escola.enturmacao.index') }}">
                            🏷️ Enturmacao
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('escola/lotacao*') ? 'active' : '' }}"
                           href="{{ route('escola.lotacao.index') }}">
                            🏷️ Lotação
                        </a>
                    </li>
                @endif

                {{-- PROFESSOR -}}
                @if(session('current_role') === 'professor')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/dashboard*') ? 'active' : '' }}"
                           href="{{ route('professor.dashboard') }}">
                            🏠 Painel
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/ofertas*') ? 'active' : '' }}"
                           href="{{ route('professor.ofertas.index') }}">
                            📚 Minhas Ofertas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/ocorrencias*') ? 'active' : '' }}"
                           href="{{ route('professor.ocorrencias.index') }}">
                            ⚠️ Ocorrências
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/relatorios*') ? 'active' : '' }}"
                           href="{{ route('professor.relatorios.index') }}">
                            📊 Relatórios
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('professor/perfil*') ? 'active' : '' }}"
                           href="{{ route('professor.perfil') }}">
                            👤 Meu Perfil
                        </a>
                    </li>
                @endif

            </ul>

            <ul class="navbar-nav ms-auto">
                @auth
                    {{-- Contexto atual -}}
                    @if(session('current_role') && session('current_school_id'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                                🎯 {{ ucfirst(session('current_role')) }}
                                @php
                                    $escolaAtual = \App\Models\Escola::find(session('current_school_id'));
                                @endphp
                                @if($escolaAtual)
                                    — {{ $escolaAtual->nome_e }}
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                {{-- Opção de trocar contexto -}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('choose.school') }}">
                                        🔄 Trocar de contexto
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @php
        
                        $nome = Auth::user()->nome_u ?? '';
                        $partes = explode(' ', trim($nome));
                        $primeiro = $partes[0] ?? '';
                        $ultimo = count($partes) > 1 ? end($partes) : '';
 
                    @endphp

                    <li class="nav-item">
                        <span class="nav-link">👤 {{ Auth::user()->nome_u ?? 'Usuário' }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-link nav-link">🚪 Sair</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                @endauth
            </ul>

        </div>
        -}}

    </div>
</nav>
--}}

{{-- Espaço para compensar navbar fixa --}}
<div style="margin-top: 100px;"></div>

{{-- Debug de mensagens --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

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


