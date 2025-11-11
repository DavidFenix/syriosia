<div class="card-body" id="turma-{{ $turma->id }}">
    @forelse($turma->diretores as $dt)
        <div class="d-flex justify-content-between align-items-center border-bottom py-1">
            <span>{{ $dt->professor->usuario->nome_u ?? 'Professor não identificado' }}</span>
            <form method="POST"
                  action="{{ route('escola.lotacao.diretor_turma.destroy', $dt->id) }}"
                  class="d-inline delete-form">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Remover diretor de turma">🗑</button>
            </form>
        </div>
    @empty
        <p class="text-muted mb-0">Nenhum diretor atribuído</p>
    @endforelse
</div>
