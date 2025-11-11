@extends('layouts.app')
@section('title','Gestão de Instituições')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Instituições / Secretarias</h1>
    <a href="{{ route('master.escolas.create') }}" class="btn btn-primary">Nova instituição</a>
  </div>
  @include('master.escolas._list', ['escolas' => $escolas, 'filtro' => $filtro])
</div>
@endsection



{{--
----------------------------------------------------
quando index.blade.php está assim, funciona
@extends('layouts.app')
@section('title','Gestão de Instituições')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Instituições / Secretarias</h1>
  <a href="{{ route('master.escolas.create') }}" class="btn btn-primary">Nova instituição</a>
</div>

<form method="get" class="row g-2 mb-3">
  <div class="col-auto">
    <select name="tipo" class="form-select" onchange="this.form.submit()">
      <option value="">Todas</option>
      <option value="mae"   {{ ($filtro ?? '') === 'mae' ? 'selected' : '' }}>Somente Secretarias (mães)</option>
      <option value="filha" {{ ($filtro ?? '') === 'filha' ? 'selected' : '' }}>Somente Escolas (filhas)</option>
    </select>
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
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('master.escolas.edit', $e) }}">Editar</a>
        <form action="{{ route('master.escolas.destroy', $e) }}" method="post" class="d-inline" onsubmit="return confirm('Excluir esta escola?');">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-outline-danger">Excluir</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center text-muted">Nenhum registro.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
--}}

{{--
@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Escolas</h1>
    @include('master.escolas._list', ['escolas' => $escolas, 'filtro' => $filtro])
</div>
@endsection
--}}

