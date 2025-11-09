<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Oferta;
use App\Models\Enturmacao;
use App\Models\Aluno;
use App\Models\Ocorrencia;

class OfertaController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        // 🔍 Vínculo do professor na escola atual
        $professor = $user->professor()->where('school_id', $schoolId)->first();
        if (!$professor) {
            return redirect()
                ->route('professor.dashboard')
                ->with('warning', '⚠️ Seu usuário não está vinculado como professor nesta escola.');
        }

        $ofertas = Oferta::with(['disciplina', 'turma'])
            ->where('professor_id', $professor->id)
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $anoLetivo)
            ->where('vigente', true)
            ->get()
            ->sortBy(fn($o) => $o->disciplina->descr_d)
            ->sortBy(fn($o) => $o->turma->serie_turma);

            

        // 📚 Ofertas do professor no ano vigente e escola logada
        //contagem correta agrupado por turma_id
            $sub = DB::table(prefix('ocorrencia') . ' as o')
                ->join(prefix('oferta') . ' as ofe', 'ofe.id', '=', 'o.oferta_id')
                ->select('ofe.turma_id', 'o.aluno_id', DB::raw('COUNT(*) AS total'))
                ->where('o.school_id', $schoolId)
                ->where('o.ano_letivo', $anoLetivo)
                ->where('o.status', 1)
                ->groupBy('ofe.turma_id', 'o.aluno_id');

            $stats = DB::query()
                ->fromSub($sub, 't')
                ->select(
                    'turma_id',
                    DB::raw('SUM(CASE WHEN total = 1 THEN 1 ELSE 0 END) AS qtd1'),
                    DB::raw('SUM(CASE WHEN total = 2 THEN 1 ELSE 0 END) AS qtd2'),
                    DB::raw('SUM(CASE WHEN total = 3 THEN 1 ELSE 0 END) AS qtd3'),
                    DB::raw('SUM(CASE WHEN total = 4 THEN 1 ELSE 0 END) AS qtd4'),
                    DB::raw('SUM(CASE WHEN total >= 5 THEN 1 ELSE 0 END) AS qtd5')
                )
                ->groupBy('turma_id')
                ->get()
                ->keyBy('turma_id');

            // Aplica contagem às ofertas
            foreach ($ofertas as $oferta) {
                $dados = $stats->get($oferta->turma_id);
                $oferta->qtd1 = $dados->qtd1 ?? 0;
                $oferta->qtd2 = $dados->qtd2 ?? 0;
                $oferta->qtd3 = $dados->qtd3 ?? 0;
                $oferta->qtd4 = $dados->qtd4 ?? 0;
                $oferta->qtd5 = $dados->qtd5 ?? 0;
            }


        return view('professor.ofertas.index', compact('ofertas'));
    }

    

    /**
     * Exibe os alunos da turma vinculada a uma oferta específica
     */
    public function alunos($ofertaId)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $oferta = Oferta::with(['disciplina', 'turma'])
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $anoLetivo)
            ->findOrFail($ofertaId);

        //🔍 Alunos enturmados na turma dessa oferta da school_id logada
        //Nesse caso, nenhuma lógica deve filtrar alunos por aluno.school_id, mas sim por enturmacao.school_id (a escola de vínculo).
        $alunos = Aluno::whereHas('enturmacao', function ($q) use ($oferta) {
            $q->where('turma_id', $oferta->turma_id)
              ->where('school_id', $oferta->school_id); // 💡 usa a escola da turma/oferta
        })
        ->withCount([
            'ocorrencias as total_ocorrencias_ativas' => function ($q) use ($schoolId) {
                $q->where('status', 1)->where('school_id', $schoolId);
            },
            'ocorrencias as total_ocorrencias_geral' => function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            }
        ])
        ->orderBy('nome_a')
        ->get();

        // $alunos = Aluno::whereHas('enturmacao', function ($q) use ($oferta) {
        //         $q->where('turma_id', $oferta->turma_id);
        //     })
        //     ->withCount([
        //         // Ocorrências ativas do aluno na escola logada
        //         'ocorrencias as total_ocorrencias_ativas' => function ($q) use ($schoolId) {
        //             $q->where('status', 1)
        //               ->where('school_id', $schoolId);
        //         },
        //         // Total geral (ativas + arquivadas) na escola logada
        //         'ocorrencias as total_ocorrencias_geral' => function ($q) use ($schoolId) {
        //             $q->where('school_id', $schoolId);
        //         }
        //     ])
        //     ->orderBy('nome_a')
        //     ->get();

        return view('professor.ofertas.alunos', compact('oferta', 'alunos'));
    }

    public function alunosPost(Request $request, Oferta $oferta)
    {
        $alunosSelecionados = $request->input('alunos', []);

        if (empty($alunosSelecionados)) {
            return back()->with('warning', 'Selecione ao menos um aluno.');
        }

        return redirect()->route('professor.ocorrencias.create', [
            'oferta_id' => $oferta->id,
            'alunos' => $alunosSelecionados
        ]);
    }
 
}
