@extends('layouts.app')
@section('title','Nova instituição')

@section('content')
<h1 class="h4 mb-3">Adicionar Escola / Secretaria</h1>

<form method="POST" action="{{ route('master.escolas.store') }}" class="row g-3">
 @csrf
 <div class="col-md-6">
   <label class="form-label">Nome*</label>
   <input name="nome_e" class="form-control" required value="{{ old('nome_e') }}">
 </div>
 <div class="col-md-3">
   <label class="form-label">INEP</label>
   <input name="inep" class="form-control" value="{{ old('inep') }}">
 </div>
 <div class="col-md-3">
   <label class="form-label">CNPJ</label>
   <input name="cnpj" class="form-control" value="{{ old('cnpj') }}">
 </div>
 <div class="col-md-4">
   <label class="form-label">Cidade</label>
   <input name="cidade" class="form-control" value="{{ old('cidade') }}">
 </div>
 <div class="col-md-4">
   <label class="form-label">Estado</label>
   <input name="estado" class="form-control" value="{{ old('estado') }}">
 </div>
 <div class="col-md-8">
   <label class="form-label">Endereço</label>
   <input name="endereco" class="form-control" value="{{ old('endereco') }}">
 </div>
 <div class="col-md-4">
   <label class="form-label">Telefone</label>
   <input name="telefone" class="form-control" value="{{ old('telefone') }}">
 </div>
 <div class="col-md-6">
   <label class="form-label">Secretaria (opcional)</label>
   <select name="secretaria_id" class="form-select">
     <option value="">— Sem secretaria (será MÃE) —</option>
     @foreach($maes as $m)
       <option value="{{ $m->id }}" {{ old('secretaria_id')==$m->id?'selected':'' }}>
         {{ $m->nome_e }}
       </option>
     @endforeach
   </select>
 </div>

 <div class="col-12">
   <button class="btn btn-primary">Salvar</button>
   <a href="{{ route('master.escolas.index') }}" class="btn btn-secondary">Voltar</a>
 </div>
</form>
@endsection
