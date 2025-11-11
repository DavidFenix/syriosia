<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turma;
use App\Models\Professor;
use App\Models\DiretorTurma;
use Illuminate\Support\Facades\DB;

class DiretorTurmaController extends Controller
{
    public function index()
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $turmas = Turma::where('school_id', $schoolId)
            ->with(['diretores.usuario'])
            ->orderBy('serie_turma')
            ->get();

        $professores = Professor::with('usuario')
            ->where('school_id', $schoolId)
            ->orderBy('id')
            ->get();

        return view('escola.lotacao.diretor_turma', compact('turmas', 'professores', 'anoLetivo'));
    }

    public function update(Request $request)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $request->validate([
            'turma_id' => 'required|integer|exists:' . prefix() . 'turma,id',
            'professores' => 'nullable|array',
        ]);

        $turmaId = $request->turma_id;

        DB::transaction(function () use ($turmaId, $schoolId, $anoLetivo, $request) {
            // Remove diretores antigos
            DiretorTurma::where('turma_id', $turmaId)
                ->where('school_id', $schoolId)
                ->where('ano_letivo', $anoLetivo)
                ->delete();

            // Adiciona novos
            if (!empty($request->professores)) {
                foreach ($request->professores as $profId) {
                    DiretorTurma::create([
                        'school_id'    => $schoolId,
                        'turma_id'     => $turmaId,
                        'professor_id' => $profId,
                        'ano_letivo'   => $anoLetivo,
                        'vigente'      => true,
                    ]);
                }
            }
        });

        // 🔄 Retorna HTML parcial atualizado do card
        $turma = \App\Models\Turma::with(['diretores.professor.usuario'])
            ->find($turmaId);

        // return response()->json([
        //     'success' => true,
        //     'html' => view('escola.lotacao._diretores_card', compact('turma'))->render()
        // ]);
        return response()->json([
            'success' => true,
            'html' => view('escola.lotacao._diretores_card', compact('turma'))->render(),
            'ids' => $turma->diretores->pluck('professor_id')->toArray() // 👈 envia lista
        ]);

    }

    public function destroy($id)
    {
        $schoolId = session('current_school_id');

        $registro = DiretorTurma::where('school_id', $schoolId)->findOrFail($id);
        $turmaId = $registro->turma_id;
        $registro->delete();

        $turma = \App\Models\Turma::with(['diretores.professor.usuario'])
            ->find($turmaId);

        // return response()->json([
        //     'success' => true,
        //     'html' => view('escola.lotacao._diretores_card', compact('turma'))->render()
        // ]);
        return response()->json([
            'success' => true,
            'html' => view('escola.lotacao._diretores_card', compact('turma'))->render(),
            'ids' => $turma->diretores->pluck('professor_id')->toArray() // 👈 envia lista
        ]);

    }

}
