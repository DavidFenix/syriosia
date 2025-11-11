<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enturmacao;
use App\Models\Aluno;
use App\Models\Turma;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EnturmacaoController extends Controller
{
    
    public function index()
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $enturmacoes = Enturmacao::with(['aluno', 'turma'])
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $anoLetivo)
            ->orderByDesc('id')
            ->get();

        return view('escola.enturmacao.index', compact('enturmacoes', 'anoLetivo'));
    }

    public function create(Request $request)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $turmas = Turma::where('school_id', $schoolId)->orderBy('serie_turma')->get();

        $alunosFiltrados = null;
        $alunosTurmaOrigem = null;

        // pesquisa geral
        if ($request->filled('nome') || $request->filled('matricula')) {
            $query = Aluno::where('school_id', $schoolId);
            if ($request->filled('nome')) {
                $query->where('nome_a', 'like', "%{$request->nome}%");
            }
            if ($request->filled('matricula')) {
                $query->where('matricula', 'like', "%{$request->matricula}%");
            }
            $alunosFiltrados = $query->orderBy('nome_a')->get();
        }

        // pesquisa por turma
        if ($request->filled('turma_origem')) {
            $alunosTurmaOrigem = Aluno::whereHas('enturmacao', function ($q) use ($request, $schoolId) {
                $q->where('turma_id', $request->turma_origem)
                  ->where('school_id', $schoolId)
                  ->where('ano_letivo', $request->ano_origem ?? date('Y') - 1);
            })->orderBy('nome_a')->get();
        }

        return view('escola.enturmacao.create', compact(
            'turmas', 'alunosFiltrados', 'alunosTurmaOrigem', 'anoLetivo'
        ));
    }

    
    public function store(Request $request)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $request->validate([
            'aluno_id' => 'required|integer|exists:' . prefix() . 'aluno,id',
            'turma_id' => 'required|integer|exists:' . prefix() . 'turma,id',
        ]);

        // Evita duplicação do vínculo no mesmo ano e escola
        $jaExiste = Enturmacao::where([
            'school_id' => $schoolId,
            'aluno_id' => $request->aluno_id,
            'ano_letivo' => $anoLetivo,
        ])->exists();

        if ($jaExiste) {
            return redirect()->route('escola.enturmacao.index')
                ->with('warning', '⚠️ Este aluno já está enturmado neste ano letivo.');
        }

        Enturmacao::create([
            'school_id' => $schoolId,
            'aluno_id' => $request->aluno_id,
            'turma_id' => $request->turma_id,
            'ano_letivo' => $anoLetivo,
            'vigente' => true,
        ]);

        return redirect()->route('escola.enturmacao.index')
            ->with('success', '✅ Aluno enturmado com sucesso!');
    }

    public function storeBatch(Request $request)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = $request->input('ano_letivo', session('ano_letivo_atual') ?? date('Y'));

        $request->validate([
            'turma_id'   => 'required|integer|exists:' . prefix() . 'turma,id',
            'alunos'     => 'required|array|min:1',
            'ano_letivo' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        try {
            DB::beginTransaction();

            $turma = Turma::find($request->turma_id);

            $totalInseridos = 0;
            $jaExistiam = 0;

            foreach ($request->alunos as $alunoId) {
                // Evita duplicação: aluno já enturmado neste ano e escola
                $existe = Enturmacao::where('aluno_id', $alunoId)
                    ->where('turma_id', $request->turma_id)
                    ->where('school_id', $schoolId)
                    ->where('ano_letivo', $anoLetivo)
                    ->exists();

                if ($existe) {
                    $jaExistiam++;
                    continue;
                }

                Enturmacao::create([
                    'aluno_id'   => $alunoId,
                    'turma_id'   => $request->turma_id,
                    'school_id'  => $schoolId,
                    'ano_letivo' => $anoLetivo,
                    'vigente'    => true,
                ]);

                $totalInseridos++;
            }

            DB::commit();

            $msg = "✅ Enturmação concluída: {$totalInseridos} aluno(s) novos adicionados.";
            if ($jaExistiam > 0) {
                $msg .= " ({$jaExistiam} já estavam enturmados nesta turma e ano.)";
            }

            return redirect()
                ->route('escola.enturmacao.index')
                ->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();

            $erro = $e->getMessage();

            // 🔍 Detecta violação de UNIQUE (erro 1062)
            if (str_contains($erro, '1062') || str_contains($erro, 'Duplicate entry')) {
                Log::warning('Tentativa de enturmar aluno duplicado', [
                    'erro' => $erro,
                    'school_id' => $schoolId,
                    'user_id' => auth()->id(),
                ]);

                return back()
                    ->withInput()
                    ->with('error', '⚠️ Um ou mais alunos já estão enturmados nesta escola e ano letivo.');
            }

            // 🔹 Outros erros genéricos
            Log::error('Erro ao enturmar em lote', [
                'erro' => $erro,
                'school_id' => $schoolId,
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', '❌ Ocorreu um erro inesperado ao enturmar alunos. Detalhes foram registrados no log.');
        }

    }

    public function edit($id)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $enturmacao = Enturmacao::with(['aluno', 'turma'])
            ->where('school_id', $schoolId)
            ->findOrFail($id);

        // turmas da escola atual (não filtramos por ano para permitir ajuste fino)
        $turmas = Turma::where('school_id', $schoolId)
            ->orderBy('serie_turma')
            ->get(['id','serie_turma','turno']);

        return view('escola.enturmacao.edit', compact('enturmacao', 'turmas', 'anoLetivo'));
    }

    public function update(Request $request, $id)
    {
        $schoolId = session('current_school_id');

        $request->validate([
            'turma_id'   => 'required|integer|exists:' . prefix() . 'turma,id',
            'ano_letivo' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'vigente'    => 'nullable|boolean',
        ]);

        $enturmacao = Enturmacao::where('school_id', $schoolId)->findOrFail($id);

        $enturmacao->turma_id   = $request->turma_id;
        // ano_letivo e aluno_id não se alteram aqui (histórico); se quiser permitir, remova este comentário e trate impacto.
        $enturmacao->vigente    = (bool) $request->boolean('vigente', false);
        $enturmacao->save();

        return redirect()->route('escola.enturmacao.index')
            ->with('success', '✅ Enturmação atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $schoolId = session('current_school_id');

        $enturmacao = Enturmacao::where('school_id', $schoolId)->findOrFail($id);
        $enturmacao->delete();

        return redirect()->route('escola.enturmacao.index')
            ->with('success', '🔗 Enturmação removida com sucesso.');
    }
}
