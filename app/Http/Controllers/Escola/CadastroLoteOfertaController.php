<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Professor;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Services\CadastroLoteOfertaService;

class CadastroLoteOfertaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!session('current_school_id')) {
                return redirect()
                    ->route('choose.school')
                    ->with('error', 'Nenhuma escola selecionada no contexto.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $schoolId = session('current_school_id');

        $professores = Professor::where('school_id', $schoolId)
            ->with('usuario')
            ->get();

        $disciplinas = Disciplina::where('school_id', $schoolId)->get();

        $turmas = Turma::where('school_id', $schoolId)->get();

        return view('escola.ofertas_lote.index', compact(
            'professores',
            'disciplinas',
            'turmas'
        ));
    }

    public function modelo()
    {
        $schoolId = session('current_school_id');

        $professores = Professor::where('school_id', $schoolId)
            ->with('usuario')
            ->get();

        $disciplinas = Disciplina::where('school_id', $schoolId)->get();

        $turmas = Turma::where('school_id', $schoolId)->get();

        return response()->streamDownload(function () use ($professores, $disciplinas, $turmas) {

            header('Content-Type: text/csv; charset=UTF-8');
            echo "\xEF\xBB\xBF"; // BOM UTF-8
            echo "sep=;\n";

            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'cpf_professor',
                'nome_professor',
                'disciplina_id',
                'descr_d',
                'turma_id',
                'serie_turma'
            ], ';');

            foreach ($professores as $p) {
                foreach ($disciplinas as $d) {
                    foreach ($turmas as $t) {

                        fputcsv($out, [
                            $p->usuario->cpf,
                            $p->usuario->nome_u,
                            $d->id,
                            $d->descr_d,
                            $t->id,
                            $t->serie_turma,
                        ], ';');
                    }
                }
            }

            fclose($out);

        }, 'modelo_ofertas.csv');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $schoolId = session('current_school_id');

        $service = new CadastroLoteOfertaService($schoolId);

        $linhas = $service->previewCSV($request->file('arquivo'));

        $payload = base64_encode(json_encode($linhas));

        return view('escola.ofertas_lote.preview', compact('linhas', 'payload'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'linhas' => 'required',
        ]);

        $decoded = json_decode(base64_decode($request->linhas), true);

        if (!is_array($decoded)) {
            return redirect()
                ->route('escola.ofertas.lote.index')
                ->with('error', 'Dados inválidos.');
        }

        $schoolId = session('current_school_id');

        $service = new CadastroLoteOfertaService($schoolId);

        $resultado = $service->importarLinhas($decoded);

        return view('escola.ofertas_lote.resultado', compact('resultado'));
    }
}
