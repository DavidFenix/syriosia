@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Escolha a Escola</h1>
    <p>Você possui vínculos em mais de uma instituição. Selecione abaixo em qual deseja entrar:</p>

    @foreach($escolas as $schoolId => $roles)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ \App\Models\Escola::find($schoolId)->nome_e ?? 'Escola #' . $schoolId }}</h5>
                <a href="{{ route('choose.role', $schoolId) }}" class="btn btn-primary">
                    Escolher papéis nesta escola
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection
