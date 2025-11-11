@extends('layouts.app')
@section('title','Nova Escola')

@section('content')
<div class="container">
  <h1>Nova Escola</h1>
  <form method="post" action="{{ route('secretaria.escolas.store') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="nome_e" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">INEP</label>
      <input type="text" name="inep" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">CNPJ</label>
      <input type="text" name="cnpj" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('secretaria.escolas.index') }}" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
@endsection
