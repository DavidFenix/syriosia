@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lotação de Diretores de Turma</h1>

    @foreach($turmas as $turma)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ $turma->serie_turma }}</strong>
                <button class="btn btn-sm btn-warning" onclick="editarTurma({{ $turma->id }})">✏️ Editar</button>
            </div>

            {{-- Lista de diretores atuais --}}
            <div class="card-body" id="turma-{{ $turma->id }}">
                @forelse($turma->diretores as $dt)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                        <span>
                            {{ $dt->professor->usuario->nome_u ?? 'Professor não identificado' }}
                        </span>
                        <form method="POST" action="{{ route('escola.lotacao.diretor_turma.destroy', $dt->id) }}" class="d-inline delete-form">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Remover diretor de turma">🗑</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">Nenhum diretor atribuído</p>
                @endforelse
            </div>

            {{-- Formulário de edição (inicialmente oculto) --}}
            <div class="card-body bg-light" id="form-editar-{{ $turma->id }}" style="display:none;">
                <form method="POST" action="{{ route('escola.lotacao.diretor_turma.update') }}">
                    @csrf
                    <input type="hidden" name="turma_id" value="{{ $turma->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Professores disponíveis</label>

                        <div class="prof-grid">
                            @foreach($professores as $p)
                                <div class="form-check mb-2">
                                    <input type="checkbox"
                                           name="professores[]"
                                           value="{{ $p->id }}"
                                           id="prof_{{ $turma->id }}_{{ $p->id }}"
                                           class="form-check-input"
                                           {{ $turma->diretores->pluck('professor_id')->contains($p->id) ? 'checked' : '' }}>
                                    <label for="prof_{{ $turma->id }}_{{ $p->id }}" class="form-check-label">
                                        {{ $p->usuario->nome_u ?? 'Professor sem nome' }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <button class="btn btn-success btn-sm">💾 Salvar</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelarEdicao({{ $turma->id }})">Cancelar</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('styles')
<style>
    .prof-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.5rem; /* espaçamento entre colunas e linhas */
    }
</style>
@endpush

@push('scripts')
<script>
function editarTurma(turmaId) {
    document.getElementById('form-editar-' + turmaId).style.display = 'block';
    document.getElementById('turma-' + turmaId).style.display = 'none';
}

function cancelarEdicao(turmaId) {
    document.getElementById('form-editar-' + turmaId).style.display = 'none';
    document.getElementById('turma-' + turmaId).style.display = 'block';
}

// Intercepta todos os formulários de edição
document.querySelectorAll('form[action*="diretor_turma/update"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const turmaId = this.querySelector('input[name="turma_id"]').value;

        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('form-editar-' + turmaId).style.display = 'none';
                document.getElementById('turma-' + turmaId).outerHTML = data.html;
                attachDeleteEvents(); // reanexa eventos
            }
        });
    });
});

// Ajax delete reutilizável
function attachDeleteEvents() {
    // Formulários de exclusão (🗑)
    document.querySelectorAll('.delete-form').forEach(form => {
        if (form.dataset.bound === "true") return;
        form.dataset.bound = "true";

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Remover este diretor de turma?')) return;

            const btn = this.querySelector('button');
            btn.disabled = true;

            fetch(this.action, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value }
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;

                if (data.success) {
                    const turmaId = this.closest('[id^="turma-"]').id.split('-')[1];

                    // atualiza o card da turma
                    document.getElementById('turma-' + turmaId).outerHTML = data.html;

                    // 🧩 Sincroniza checkboxes
                    const formEditar = document.getElementById('form-editar-' + turmaId);
                    if (formEditar && data.ids) {
                        const ativos = data.ids.map(id => parseInt(id));

                        formEditar.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                            const professorId = parseInt(cb.value);
                            cb.checked = ativos.includes(professorId); // marca só os que continuam
                        });
                    }



                    attachAllEvents();
                }else {
                    alert('Erro ao excluir o diretor.');
                }

                // if (data.success) {
                //     const turmaId = this.closest('[id^="turma-"]').id.split('-')[1];
                //     document.getElementById('turma-' + turmaId).outerHTML = data.html;

                //     // 🧩 Extra: desmarcar o checkbox correspondente no form de edição
                //     const profIdMatch = this.action.match(/\/(\d+)$/);
                //     if (profIdMatch) {
                //         const deletedId = profIdMatch[1];
                //         const formEditar = document.getElementById('form-editar-' + turmaId);
                //         if (formEditar) {
                //             // procura o professor_id dentro da turma e desmarca
                //             formEditar.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                //                 if (cb.checked && cb.value == deletedId) {
                //                     cb.checked = false;
                //                 }
                //             });
                //         }
                //     }

                //     attachAllEvents();
                // } else {
                //     alert('Erro ao excluir o diretor.');
                // }
            });
        });
    });

}

// Executa no carregamento inicial
attachDeleteEvents();
</script>
@endpush
