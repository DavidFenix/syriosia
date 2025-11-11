{{-- resources/views/master/escolas/_list.blade.php --}}

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">🏫 Lista de Escolas e Secretarias</h4>
  {{-- <a href="{{ route('master.escolas.create') }}" class="btn btn-success">+ Nova Escola</a> --}}
</div>

<div class="card shadow-sm">
  <div class="card-body">

    <table id="escolasTable" class="table table-striped table-bordered align-middle w-100">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>INEP</th>
          <th>CNPJ</th>
          <th>Secretaria (Mãe)</th>
          <th>Data</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>

      <tbody>
        @php $auth = auth()->user(); @endphp

        @foreach($escolas as $e)
          <tr>
            <td>{{ $e->id }}</td>
            <td>
              {{ $e->nome_e }}
              @if($e->is_master)
                <span class="badge bg-warning text-dark ms-1">Master</span>
              @elseif(is_null($e->secretaria_id))
                <span class="badge bg-primary ms-1">Secretaria</span>
              @else
                <span class="badge bg-info ms-1">Escola</span>
              @endif
            </td>
            <td>{{ $e->inep ?? '-' }}</td>
            <td>{{ $e->cnpj ?? '-' }}</td>
            <td>{{ optional($e->mae)->nome_e ?? '—' }}</td>
            <td>{{ $e->created_at_br ?? '-' }}</td>

            {{-- AÇÕES --}}
            <td class="text-end">
              @if(!$e->is_master)
                <a href="{{ route('master.escolas.detalhes', $e->id) }}" class="btn btn-sm btn-outline-info">
                  🔍 Detalhes
                </a>
                <a href="{{ route('master.escolas.edit', $e) }}" class="btn btn-sm btn-outline-secondary">
                  Editar
                </a>
                <form action="{{ route('master.escolas.destroy', $e) }}" method="post" class="d-inline"
                      onsubmit="return confirm('Excluir esta escola?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
              @else
                @if($auth && $auth->is_super_master)
                  <a href="{{ route('master.escolas.edit', $e) }}" class="btn btn-sm btn-warning"
                     title="Editar escola principal (apenas Super Master)">
                    ⚙️ Editar Master
                  </a>
                @else
                  <button class="btn btn-sm btn-secondary" disabled
                          title="Somente o Super Master pode editar a escola principal">
                    🔒
                  </button>
                @endif
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>

      {{-- 🔍 filtros individuais nas colunas --}}
      <tfoot>
        <tr>
          <th></th>
          <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar nome"></th>
          <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar INEP"></th>
          <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar CNPJ"></th>
          <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar secretaria"></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    // inicializa com o script global do public/js/datatables-init.js
    // colunas filtráveis: Nome(1), CPF(2), Escola(3), Roles(4), CNPJ(5)
    initDataTable('#escolasTable', { order: [[1, 'asc']] }, [1, 2, 3, 4, 5]);
});
</script>
@endpush


{{--
<form method="get" class="row g-2 mb-3">
  <div class="col-auto">
    <select name="tipo" class="form-select">
      <option value="">Todas</option>
      <option value="mae"   {{ ($filtro ?? '') === 'mae' ? 'selected' : '' }}>Somente Secretarias (mães)</option>
      <option value="filha" {{ ($filtro ?? '') === 'filha' ? 'selected' : '' }}>Somente Escolas (filhas)</option>
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary">Filtrar</button>
  </div>

</form>

<table class="table table-sm table-striped align-middle">
  <thead>
    <tr>
      <th>#</th>
      <th>Nome</th>
      <th>INEP</th>
      <th>CNPJ</th>
      <th>Secretaria</th>
      <th class="text-end">Ações</th>
    </tr>
  </thead>
  <tbody>
  @forelse($escolas as $e)
    <tr>
      <td>{{ $e->id }}</td>
      <td>{{ $e->nome_e }}</td>
      <td>{{ $e->inep }}</td>
      <td>{{ $e->cnpj }}</td>
      <td>{{ optional($e->mae)->nome_e }}</td>
      <td class="text-end">
        @php
            $auth = auth()->user();
        @endphp

        {{--regra:Bloqueia edição da escola master por usuario não-super_master-}}
        {{-- Se for escola normal -}}
        @if(!$e->is_master)
            <a href="{{ route('master.escolas.detalhes', $e->id) }}" 
               class="btn btn-sm btn-outline-info">
               🔍 Detalhes
            </a>
            <a class="btn btn-sm btn-outline-secondary"
               href="{{ route('master.escolas.edit', $e) }}">
                Editar
            </a>
            <form action="{{ route('master.escolas.destroy', $e) }}" method="post"
                  class="d-inline"
                  onsubmit="return confirm('Excluir esta escola?');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
            </form>

        {{-- Se for a escola master -}}
        @else
           @if($auth && $auth->is_super_master)
                <a class="btn btn-sm btn-warning"
                   href="{{ route('master.escolas.edit', $e) }}"
                   title="Editar escola principal (apenas Super Master)">
                    ⚙️ Editar Master
                </a>
            @else
                <button class="btn btn-sm btn-secondary" disabled
                        title="Somente o Super Master pode editar a escola principal">
                    🔒
                </button>
            @endif
        @endif


        {{--
        @if(!$e->is_master)
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('master.escolas.edit', $e) }}">Editar</a>
            @if($e->id !== 1)
                <form action="{{ route('master.escolas.destroy', $e) }}" method="post" class="d-inline" onsubmit="return confirm('Excluir esta escola?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
            @else
                <button class="btn btn-sm btn-secondary" disabled title="Você não pode excluir a escola principal">🔒</button>
            @endif
        @else
            <button class="btn btn-sm btn-secondary" disabled title="A escola principal não pode ser editada nem excluída">
                🔒
            </button>
        @endif-}}
      </td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center text-muted">Nenhum registro.</td></tr>
  @endforelse
  </tbody>
</table>
--}}



{{-- Lista de Escolas -}}
<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="{{ route('master.escolas.index') }}">
        <select name="filtro" class="form-select d-inline w-auto">
            <option value="" {{ $filtro===''?'selected':'' }}>Todas</option>
            <option value="mae" {{ $filtro==='mae'?'selected':'' }}>Somente Secretarias (mães)</option>
            <option value="filha" {{ $filtro==='filha'?'selected':'' }}>Somente Escolas (filhas)</option>
        </select>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    <a href="{{ route('master.escolas.create') }}" class="btn btn-success">Nova Escola</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>INEP</th>
            <th>CNPJ</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($escolas as $escola)
            <tr>
                <td>{{ $escola->id }}</td>
                <td>{{ $escola->nome_e }}</td>
                <td>{{ $escola->inep }}</td>
                <td>{{ $escola->cnpj }}</td>
                <td>
                    <a href="{{ route('master.escolas.edit', $escola->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('master.escolas.destroy', $escola->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


{{-- Lista de Escolas -}}
<div>
    <form method="GET" action="{{ route('master.escolas.index') }}" class="mb-3">
        <select name="filtro" class="form-select d-inline w-auto">
            <option value="" {{ $filtro===''?'selected':'' }}>Todas</option>
            <option value="mae" {{ $filtro==='mae'?'selected':'' }}>Somente Secretarias (mães)</option>
            <option value="filha" {{ $filtro==='filha'?'selected':'' }}>Somente Escolas (filhas)</option>
        </select>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>INEP</th>
                <th>CNPJ</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($escolas as $escola)
                <tr>
                    <td>{{ $escola->id }}</td>
                    <td>{{ $escola->nome_e }}</td>
                    <td>{{ $escola->inep }}</td>
                    <td>{{ $escola->cnpj }}</td>
                    <td>
                        <a href="{{ route('master.escolas.edit', $escola->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('master.escolas.destroy', $escola->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
--}}