@extends('layouts.app')
@section('title','Editar instituição')

@section('content')
<h1 class="h4 mb-3">Editar Escola / Secretaria</h1>

@php
    $auth = auth()->user();
    $isMaster = $auth && ($auth->is_super_master || $auth->hasRole('master')); // ajuste conforme seu sistema
@endphp

<form method="post" class="row g-3" action="{{ route('master.escolas.update', $escola) }}">
 @csrf
 @method('PUT')

 <div class="col-md-6">
   <label class="form-label">Nome*</label>
   <input name="nome_e" class="form-control" required value="{{ old('nome_e', $escola->nome_e) }}">
 </div>

 <div class="col-md-3">
   <label class="form-label">INEP</label>
   <input name="inep" class="form-control" value="{{ old('inep', $escola->inep) }}">
 </div>

 <div class="col-md-3">
   <label class="form-label">CNPJ</label>
   <input name="cnpj" class="form-control" value="{{ old('cnpj', $escola->cnpj) }}">
 </div>

 <div class="col-md-4">
   <label class="form-label">Cidade</label>
   <input name="cidade" class="form-control" value="{{ old('cidade', $escola->cidade) }}">
 </div>

 <div class="col-md-4">
   <label class="form-label">Estado</label>
   <input name="estado" class="form-control" value="{{ old('estado', $escola->estado) }}">
 </div>

 <div class="col-md-8">
   <label class="form-label">Endereço</label>
   <input name="endereco" class="form-control" value="{{ old('endereco', $escola->endereco) }}">
 </div>

 <div class="col-md-4">
   <label class="form-label">Telefone</label>
   <input name="telefone" class="form-control" value="{{ old('telefone', $escola->telefone) }}">
 </div>

 {{-- ⚙️ Regra de hierarquia --}}
 @if($escola->is_master)
     {{-- 🔒 É a escola master principal: não pode ter mãe nem trocar --}}
     <div class="col-md-6">
       <label class="form-label">Tipo</label>
       <input class="form-control" value="Escola Master (fixa)" disabled>
     </div>

 @elseif($escola->secretaria_id === null)
     {{-- 🔒 É uma secretaria (mãe): não pode deixar de ser mãe --}}
     <div class="col-md-6">
       <label class="form-label">Secretaria vinculada</label>
       <input class="form-control" value="— Secretaria (não vinculada a outra) —" disabled>
       <input type="hidden" name="secretaria_id" value="">
     </div>

 @else
     {{-- 🏫 É uma escola (filha) --}}
     <div class="col-md-6">
       <label class="form-label">Secretaria responsável</label>

       @if($isMaster)
         {{-- 👑 Somente usuários master podem alterar a mãe --}}
         <select name="secretaria_id" class="form-select">
           @foreach($maes as $m)
             <option value="{{ $m->id }}" {{ old('secretaria_id', $escola->secretaria_id)==$m->id?'selected':'' }}>
               {{ $m->nome_e }}
             </option>
           @endforeach
         </select>
       @else
         {{-- 🚫 Outros usuários veem, mas não podem alterar --}}
         <select class="form-select" disabled>
           @foreach($maes as $m)
             <option value="{{ $m->id }}" {{ $escola->secretaria_id==$m->id?'selected':'' }}>
               {{ $m->nome_e }}
             </option>
           @endforeach
         </select>
         <input type="hidden" name="secretaria_id" value="{{ $escola->secretaria_id }}">
       @endif
     </div>
 @endif

 <div class="col-12">
   <button class="btn btn-primary">Salvar</button>
   <a href="{{ route('master.escolas.index') }}" class="btn btn-secondary">Voltar</a>
 </div>
</form>
@endsection



{{--
vamos alterar esse arquivo para
  --se for uma secretaria não pode deixar de ser secretaria)
  --se for escola não pode deixar de ser escola
  --o que pode acontecer aqui é a escola trocar de mãe
  --quem pode fazer isso é um usuario master
  --se for preciso pode esconder o select do usuario, filtrar, etc

@extends('layouts.app')
@section('title','Editar instituição')

@section('content')
<h1 class="h4 mb-3">Editar Escola / Secretaria</h1>

<form method="post" class="row g-3" action="{{ route('master.escolas.update', $escola) }}">
 @csrf
 @method('PUT')

 <div class="col-md-6">
   <label class="form-label">Nome*</label>
   <input name="nome_e" class="form-control" required value="{{ old('nome_e', $escola->nome_e) }}">
 </div>
 <div class="col-md-3">
   <label class="form-label">INEP</label>
   <input name="inep" class="form-control" value="{{ old('inep', $escola->inep) }}">
 </div>
 <div class="col-md-3">
   <label class="form-label">CNPJ</label>
   <input name="cnpj" class="form-control" value="{{ old('cnpj', $escola->cnpj) }}">
 </div>
 <div class="col-md-4">
   <label class="form-label">Cidade</label>
   <input name="cidade" class="form-control" value="{{ old('cidade', $escola->cidade) }}">
 </div>
 <div class="col-md-4">
   <label class="form-label">Estado</label>
   <input name="estado" class="form-control" value="{{ old('estado', $escola->estado) }}">
 </div>
 <div class="col-md-8">
   <label class="form-label">Endereço</label>
   <input name="endereco" class="form-control" value="{{ old('endereco', $escola->endereco) }}">
 </div>
 <div class="col-md-4">
   <label class="form-label">Telefone</label>
   <input name="telefone" class="form-control" value="{{ old('telefone', $escola->telefone) }}">
 </div>

 <div class="col-md-6">
   <label class="form-label">Vincular a uma Secretaria (opcional)</label>
   <select name="secretaria_id" class="form-select">
     <option value="">— Sem secretaria (é MÃE) —</option>
     @foreach($maes as $m)
       <option value="{{ $m->id }}" {{ old('secretaria_id', $escola->secretaria_id)==$m->id?'selected':'' }}>
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

🧩 Regras solicitadas

🔒 Se for uma Secretaria (mãe):

não pode deixar de ser secretaria → secretaria_id fixo em null;

o select não aparece.

🏫 Se for uma Escola (filha):

não pode deixar de ser escola → não pode limpar secretaria_id;

só pode trocar de mãe (ou seja, escolher outra secretaria);

e apenas usuários master podem alterar a mãe.

👑 Se o usuário não for master:

o select fica desabilitado (somente leitura).

--}}
