@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuários da Escola</h1>

    <a href="{{ route('escola.usuarios.create') }}" class="btn btn-primary mb-3">➕ Novo Usuário</a>

    <table class="table table-striped align-middle" id="tabela-usuarios-escola">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Status</th>
                <th>Roles</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
        @php
            use App\Models\Escola;

            $auth = auth()->user();
            $schoolId = session('current_school_id');
            $authTemRoleEscola = $auth->roles()
                ->wherePivot('school_id', $schoolId)
                ->where('role_name', 'escola')
                ->exists();
        @endphp

        @forelse($usuarios as $u)
            @php
                $ehMesmoUsuario = $auth->id === $u->id;
                $isNativo = $u->school_id == $schoolId;

                // Carrega as roles com escola de origem
                $rolesComOrigem = $u->roles->map(function ($r) {
                    return [
                        'nome' => $r->role_name,
                        'school_id' => $r->pivot->school_id
                    ];
                });

                $bloqueadoPorHierarquia = $rolesComOrigem->pluck('nome')->intersect(['master','secretaria'])->isNotEmpty();

                // Tem role escola na escola atual
                $temRoleEscolaAqui = $u->roles()
                    ->wherePivot('school_id', $schoolId)
                    ->where('role_name', 'escola')
                    ->exists();

                // O logado tem role escola nesta escola
                $authTemRoleEscolaAqui = $auth->roles()
                    ->wherePivot('school_id', $schoolId)
                    ->where('role_name', 'escola')
                    ->exists();

                // Só visualiza se for colega gestor da mesma escola ou master/secretaria
                $soVisualizar = (
                    ($authTemRoleEscolaAqui && $temRoleEscolaAqui && !$ehMesmoUsuario)
                    || $bloqueadoPorHierarquia
                );
            @endphp

            <tr class="{{ $ehMesmoUsuario ? 'table-secondary' : '' }}">
                <td>{{ $u->id }}</td>
                <td>{{ $u->nome_u }}</td>
                <td>{{ $u->cpf }}</td>
                <td>
                    @if($u->status)
                        <span class="badge bg-success">Ativo</span>
                    @else
                        <span class="badge bg-danger">Inativo</span>
                    @endif
                </td>

                {{-- 🏷️ Roles com indicação da escola de origem --}}
                <td>
                    @foreach($rolesComOrigem as $r)
                        @php
                            $color = match($r['nome']) {
                                'master' => 'danger',
                                'secretaria' => 'primary',
                                'escola' => 'info',
                                'professor' => 'success',
                                'aluno' => 'secondary',
                                default => 'dark'
                            };

                            $escolaOrigem = Escola::find($r['school_id']);
                            $nomeEscolaOrigem = $escolaOrigem?->nome_e ?? 'Desconhecida';
                            $ehDaEscolaAtual = ($r['school_id'] == $schoolId);
                            $icone = $ehDaEscolaAtual ? '🏫' : '🌐';
                            $tooltip = $ehDaEscolaAtual
                                ? "Pertence à escola atual ({$nomeEscolaOrigem})"
                                : "De outra escola ({$nomeEscolaOrigem})";
                        @endphp

                        <span class="badge bg-{{ $color }}" title="{{ $tooltip }}">
                            {{ $icone }} {{ ucfirst($r['nome']) }}
                        </span>
                    @endforeach
                </td>

                {{-- 🎛️ Ações --}}
                <td class="text-end">
                    {{-- Caso 1️⃣: Usuário logado (pode editar senha e roles) --}}
                    @if($ehMesmoUsuario)
                        <a href="{{ route('escola.usuarios.edit', $u) }}"
                           class="btn btn-sm btn-success me-1"
                           title="Alterar sua senha">✏️</a>
                        <a href="{{ route('escola.usuarios.roles.edit', $u) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="Gerenciar suas roles">⚙️</a>

                    {{-- Caso 2️⃣: Protegido (master, secretaria, ou outro gestor da mesma escola) --}}
                    @elseif($soVisualizar)
                        <a href="{{ route('escola.usuarios.edit', $u) }}"
                           class="btn btn-sm btn-secondary"
                           title="Somente visualização">👁️</a>

                    {{-- Caso 3️⃣: Usuário comum ou nativo da escola --}}
                    @else
                        {{-- ✅ Nativos podem usar todos os botões --}}
                        <a href="{{ route('escola.usuarios.edit', $u) }}"
                           class="btn btn-sm btn-warning me-1"
                           title="Editar usuário">✏️</a>

                        <a href="{{ route('escola.usuarios.roles.edit', $u) }}"
                           class="btn btn-sm btn-outline-primary me-1"
                           title="Gerenciar roles">⚙️</a>

                        <form action="{{ route('escola.usuarios.destroy', $u) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Remover este usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Excluir usuário">🗑</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Nenhum usuário encontrado</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Aplica o DataTable com filtro nas colunas Nome(1), CPF(2), Status(3), Roles(4)
    initDataTable('#tabela-usuarios-escola', {
        order: [[1, 'asc']],
        pageLength: 10
    }, [1, 2, 3, 4]);
});
</script>
@endpush



{{--

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuários da Escola</h1>

    <a href="{{ route('escola.usuarios.create') }}" class="btn btn-primary mb-3">➕ Novo Usuário</a>

    <table class="table table-striped align-middle" id="tabela-usuarios-escola">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Status</th>
                <th>Roles</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
        @php
            $auth = auth()->user();
            $schoolId = session('current_school_id');
            $authTemRoleEscola = $auth->roles()
                ->wherePivot('school_id', $schoolId)
                ->where('role_name', 'escola')
                ->exists();
        @endphp

        @forelse($usuarios as $u)
            @php
                $roles = $u->roles->pluck('role_name')->toArray();
                $ehMesmoUsuario = $auth->id === $u->id;
                $temRoleEscolaAlvo = in_array('escola', $roles);
                $bloqueadoPorHierarquia = in_array('master', $roles) || in_array('secretaria', $roles);

                $soVisualizar = (
                    ($authTemRoleEscola && $temRoleEscolaAlvo && !$ehMesmoUsuario)
                    || $bloqueadoPorHierarquia
                );
            @endphp

            <tr class="{{ $ehMesmoUsuario ? 'table-secondary' : '' }}">
                <td>{{ $u->id }}</td>
                <td>{{ $u->nome_u }}</td>
                <td>{{ $u->cpf }}</td>
                <td>
                    @if($u->status)
                        <span class="badge bg-success">Ativo</span>
                    @else
                        <span class="badge bg-danger">Inativo</span>
                    @endif
                </td>
                <td>
                    @foreach($roles as $r)
                        @php
                            $color = match($r) {
                                'master' => 'danger',
                                'secretaria' => 'primary',
                                'escola' => 'info',
                                'professor' => 'success',
                                'aluno' => 'secondary',
                                default => 'dark'
                            };
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ ucfirst($r) }}</span>
                    @endforeach
                </td>

                <td class="text-end">
                    {{-- Caso 1️⃣: Usuário logado (pode editar senha e roles) -}}
                    @if($ehMesmoUsuario)
                        <a href="{{ route('escola.usuarios.edit', $u) }}"
                           class="btn btn-sm btn-success me-1"
                           title="Alterar sua senha">✏️</a>
                        <a href="{{ route('escola.usuarios.roles.edit', $u) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="Gerenciar suas roles">⚙️</a>

                    {{-- Caso 2️⃣: Protegido (master, secretaria, outro gestor) -}}
                    @elseif($soVisualizar)
                        <a href="{{ route('escola.usuarios.edit', $u) }}"
                           class="btn btn-sm btn-secondary"
                           title="Somente visualização">👁️</a>

                    {{-- Caso 3️⃣: Usuário comum (professor, aluno etc.) -}}
                    @else
                        <a href="{{ route('escola.usuarios.edit', $u) }}"
                           class="btn btn-sm btn-warning me-1"
                           title="Editar usuário">✏️</a>

                        <a href="{{ route('escola.usuarios.roles.edit', $u) }}"
                           class="btn btn-sm btn-outline-primary me-1"
                           title="Gerenciar roles">⚙️</a>

                        <form action="{{ route('escola.usuarios.destroy', $u) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Remover este usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Excluir usuário">🗑</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Nenhum usuário encontrado</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Aplica o DataTable com filtro nas colunas Nome(1), CPF(2), Status(3), Roles(4)
    initDataTable('#tabela-usuarios-escola', {
        order: [[1, 'asc']],
        pageLength: 10 // inicia com 10 registros por página
    }, [1, 2, 3, 4]);
});
</script>
@endpush
--}}










{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuários da Escola</h1>
    <a href="{{ route('escola.usuarios.create') }}" class="btn btn-primary mb-3">➕ Novo Usuário</a>

    <table class="table table-striped" id="tabela-usuarios-escola">
        <thead>
            <tr>
                <th>ID</th><th>Nome</th><th>CPF</th><th>Status</th><th>Roles</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @forelse($usuarios as $u)
            <tr>
                <td>{{ $u->id }}</td>
                <td>{{ $u->nome_u }}</td>
                <td>{{ $u->cpf }}</td>
                <td>{{ $u->status ? 'Ativo':'Inativo' }}</td>
                <td>{{ implode(', ', $u->roles->pluck('role_name')->toArray()) }}</td>
                <td>
                    
                    @if($u->id !== auth()->id())
                        <a href="{{ route('escola.usuarios.edit',$u) }}" class="btn btn-sm btn-warning">✏️</a>
                        <form action="{{ route('escola.usuarios.destroy', $u) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Remover este usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">🗑</button>
                        </form>
                    @else
                        <button class="btn btn-sm btn-secondary" disabled title="Você não pode editar a si mesmo aqui">🔒</button>
                        <button class="btn btn-sm btn-secondary" disabled title="Você não pode excluir a si mesmo">🔒</button>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Nenhum usuário</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // inicializa com o script global do public/js/datatables-init.js
    // colunas filtráveis: Nome(1), CPF(2), Escola(3), Roles(4), CNPJ(5)
    initDataTable('#tabela-usuarios-escola', { order: [[1, 'asc']] }, [1, 2, 3, 4]);
});
</script>
@endpush
--}}








{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Usuários da Escola</h1>
    <a href="{{ route('escola.usuarios.create') }}" class="btn btn-primary mb-3">Novo Usuário</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Status</th>
                <th>Roles</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @foreach($usuarios as $u)
            <tr>
                <td>{{ $u->nome_u }}</td>
                <td>{{ $u->cpf }}</td>
                <td>{{ $u->status ? 'Ativo' : 'Inativo' }}</td>
                <td>
                    @foreach($u->roles as $r)
                        <span class="badge bg-info">{{ $r->role_name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('escola.usuarios.edit',$u) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('escola.usuarios.destroy',$u) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Excluir este usuário?')">Excluir</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
--}}