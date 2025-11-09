@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Escolha sua Escola</h1>
    <ul class="list-group">
        @foreach($escolas as $schoolId => $roles)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ \App\Models\Escola::find($schoolId)->nome_e ?? "Escola #$schoolId" }}
                <a href="{{ route('choose.role', $schoolId) }}" class="btn btn-sm btn-primary">
                    Selecionar
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endsection
