@extends('layouts.app')
@section('title', 'Escolas da Secretaria')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">🏫 Escolas vinculadas à {{ $secretaria->nome_e }}</h1>
    <a href="{{ route('secretaria.escolas.create') }}" class="btn btn-primary">+ Nova Escola</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table id="tabela-escolas" class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Nome da Escola</th>
                    <th>Cidade</th>
                    <th>Data de Criação</th>
                    <th>INEP</th>
                    <th>CNPJ</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($escolas as $index => $e)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $e->nome_e }}</td>
                        <td>{{ $e->cidade ?? '-' }}</td>
                        <td>{{ optional($e->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $e->inep }}</td>
                        <td>{{ $e->cnpj }}</td>
                        <td class="text-end">
                            <a href="{{ route('secretaria.escolas.edit', $e) }}" class="btn btn-sm btn-outline-secondary">
                                ✏️ Editar
                            </a>
                            <form action="{{ route('secretaria.escolas.destroy', $e) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Excluir esta escola?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">🗑️ Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhuma escola cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Nome da Escola</th>
                    <th>Cidade</th>
                    <th>Data de Criação</th>
                    <th>INEP</th>
                    <th>CNPJ</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // inicializa com o script global do public/js/datatables-init.js
    // colunas filtráveis: Nome(1), Cidade(2), Data(3), INEP(4), CNPJ(5)
    initDataTable('#tabela-escolas', { order: [[1, 'asc']] }, [1, 2, 3, 4, 5]);
});
</script>
@endpush




{{--
@extends('layouts.app')
@section('title','Escolas da Secretaria')

@section('content')

<div class="container">
    <h1>Painel da Secretaria - Escolas</h1>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Escolas vinculadas à {{ $secretaria->nome_e }}</h1>
  <a href="{{ route('secretaria.escolas.create') }}" class="btn btn-primary">Nova escola</a>
</div>

<table class="table table-sm table-striped align-middle">
  <thead>
    <tr>
      <th>#</th>
      <th>Nome</th>
      <th>INEP</th>
      <th>CNPJ</th>
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
      <td class="text-end">
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('secretaria.escolas.edit', $e) }}">Editar</a>
        <form action="{{ route('secretaria.escolas.destroy', $e) }}" method="post" class="d-inline" onsubmit="return confirm('Excluir esta escola?');">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-outline-danger">Excluir</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="5" class="text-center text-muted">Nenhuma escola cadastrada.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
--}}