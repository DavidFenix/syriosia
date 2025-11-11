<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Oferta;
use App\Models\Professor;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class LotacaoController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');
        // $professores = Professor::with('usuario')
        //     ->where('school_id', $schoolId)
        //     ->orderByDesc('id')
        //     ->get();
        $professores = Professor::with('usuario')
                ->where('school_id', $schoolId)
                ->orderBy(
                    Usuario::select('nome_u')
                        ->whereColumn('syrios_usuario.id', 'syrios_professor.usuario_id')
                        ->limit(1)
                )
                ->get();


        $professorSelecionado = $request->input('professor_id');
        $ofertas = collect();

        if ($professorSelecionado) {
            $ofertas = Oferta::with(['disciplina', 'turma'])
                ->where('school_id', $schoolId)
                ->where('professor_id', $professorSelecionado)
                ->where('ano_letivo', $anoLetivo)
                ->orderBy('disciplina_id')
                ->orderBy('turma_id') // 🔹 garante ordem interna
                ->get()
                // 🔹 reordena pelo nome da turma dentro do grupo
                ->sortBy(fn($o) => $o->turma->serie_turma)
                ->groupBy('disciplina_id');
        }



        //sql_dump($ofertas);

        return view('escola.lotacao.index', compact(
            'professores',
            'professorSelecionado',
            'ofertas',
            'anoLetivo'
        ));
    }

    public function create(Request $request)
    {
        $schoolId = session('current_school_id');
        $professorId = $request->input('professor_id');

        $disciplinas = Disciplina::where('school_id', $schoolId)->orderBy('descr_d')->get();
        $turmas = Turma::where('school_id', $schoolId)->orderBy('serie_turma')->get();

        return view('escola.lotacao.create', compact('disciplinas', 'turmas', 'professorId'));
    }

    public function store(Request $request)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $request->validate([
            'professor_id' => 'required|integer|exists:' . prefix() . 'professor,id',
            'disciplina_id' => 'required|integer|exists:' . prefix() . 'disciplina,id',
            'turmas' => 'required|array|min:1',
        ]);

        foreach ($request->turmas as $turmaId) {
            Oferta::updateOrCreate([
                'school_id'     => $schoolId,
                'professor_id'  => $request->professor_id,
                'disciplina_id' => $request->disciplina_id,
                'turma_id'      => $turmaId,
                'ano_letivo'    => $anoLetivo,
            ], [
                'vigente'       => true,
                'status'        => 1,
            ]);
        }

        return redirect()
            ->route('escola.lotacao.index', ['professor_id' => $request->professor_id])
            ->with('success', '✅ Oferta registrada com sucesso.');
    }

    public function edit($disciplinaId, Request $request)
    {
        $schoolId = session('current_school_id');
        $professorId = $request->input('professor_id');

        $disciplina = Disciplina::findOrFail($disciplinaId);
        $turmas = Turma::where('school_id', $schoolId)->orderBy('serie_turma')->get();

        $turmasAtuais = Oferta::where('school_id', $schoolId)
            ->where('professor_id', $professorId)
            ->where('disciplina_id', $disciplinaId)
            ->pluck('turma_id')
            ->toArray();

        return view('escola.lotacao.edit', compact(
            'disciplina',
            'turmas',
            'turmasAtuais',
            'professorId'
        ));
    }

    public function update(Request $request, $disciplinaId)
    {
        $schoolId = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $request->validate([
            'professor_id' => 'required|integer|exists:' . prefix() . 'professor,id',
            'turmas'       => 'nullable|array',
        ]);

        // remove todas ofertas dessa disciplina do professor
        Oferta::where('school_id', $schoolId)
            ->where('professor_id', $request->professor_id)
            ->where('disciplina_id', $disciplinaId)
            ->delete();

        // recria conforme turmas marcadas
        if ($request->filled('turmas')) {
            foreach ($request->turmas as $turmaId) {
                Oferta::create([
                    'school_id'     => $schoolId,
                    'professor_id'  => $request->professor_id,
                    'disciplina_id' => $disciplinaId,
                    'turma_id'      => $turmaId,
                    'ano_letivo'    => $anoLetivo,
                    'vigente'       => true,
                    'status'        => 1,
                ]);
            }
        }

        return redirect()
            ->route('escola.lotacao.index', ['professor_id' => $request->professor_id])
            ->with('success', '✏️ Oferta atualizada com sucesso.');
    }
}
