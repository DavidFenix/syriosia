@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-3">Confirmar exclusão</h4>

    <div class="alert alert-warning">
        <strong>Atenção!</strong> Você está prestes a excluir o usuário:
        <div class="fs-5 mt-1 text-danger">{{ $usuario->nome_u }}</div>
    </div>

    <div class="card mt-3 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Registros vinculados</h5>

            {{-- lista de vínculos resumidos --}}
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Professor vinculado
                    <span class="badge bg-primary rounded-pill">{{ $vinculos['professor'] }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Notificações
                    <span class="badge bg-primary rounded-pill">{{ $vinculos['notificacao'] }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Sessões ativas
                    <span class="badge bg-primary rounded-pill">{{ $vinculos['sessao'] }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Vínculos de roles
                    <span class="badge bg-primary rounded-pill">{{ $vinculos['roles'] }}</span>
                </li>
            </ul>

            {{-- escolas vinculadas --}}
            @if($escolasVinculadas->count() > 0)
                <h5 class="mt-4">Escolas vinculadas</h5>
                <ul class="list-group mb-3">
                    @foreach($escolasVinculadas as $escola)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $escola->nome_e }}
                            @if($escola->is_master)
                                <span class="badge bg-danger">Master</span>
                            @else
                                <span class="badge bg-secondary">Regular</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Nenhuma escola vinculada a este usuário.</p>
            @endif

            @php $total = array_sum($vinculos); @endphp

            @if($total > 0)
                <div class="alert alert-danger">
                    <strong>Este usuário possui {{ $total }} registro(s) dependente(s).</strong><br>
                    É necessário remover esses vínculos antes de prosseguir com a exclusão.
                </div>
            @else
                <div class="alert alert-success">
                    Nenhum vínculo encontrado. É seguro excluir este usuário.
                </div>
            @endif

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('master.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>

                @if($total === 0)
                    <form action="{{ route('master.usuarios.destroy', $usuario) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Excluir definitivamente</button>
                    </form>
                @else
                    <button class="btn btn-danger" disabled>Exclusão bloqueada</button>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
