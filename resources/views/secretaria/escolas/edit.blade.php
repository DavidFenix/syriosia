@extends('layouts.app')
@section('title','Editar Escola')

@section('content')
<div class="container">
  <h1>Editar Escola</h1>
  <form method="post" action="{{ route('secretaria.escolas.update', $escola) }}">
    @csrf @method('PUT')
    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="nome_e" class="form-control" value="{{ $escola->nome_e }}" required>
    </div>
    <div class="mb-3">
      <label class="form-label">INEP</label>
      <input type="text" name="inep" class="form-control" value="{{ $escola->inep }}">
    </div>
    <div class="mb-3">
      <label class="form-label">CNPJ</label>
      <input type="text" name="cnpj" class="form-control" value="{{ $escola->cnpj }}">
    </div>
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('secretaria.escolas.index') }}" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
@endsection
