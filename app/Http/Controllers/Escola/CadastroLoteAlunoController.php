<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turma;
use App\Services\CadastroLoteAlunoService;

class CadastroLoteAlunoController extends Controller
{
    public function __construct()
    {
        // Já está protegido por:
        //  - auth
        //  - ensure.context
        //  - ensure.role.escola (no grupo de rotas)
        // aqui só garantimos school_id presente
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

        $turmas = Turma::where('school_id', $schoolId)
            ->orderBy('serie_turma')
            ->orderBy('turno')
            ->get();

        return view('escola.alunos_lote.index', compact('turmas'));
    }

    /**
     * Gera o modelo CSV com turmas da escola.
     * As colunas serie_turma e turno são apenas informativas (ignoradas no processamento).
     */
    public function modelo()
    {
        $schoolId = session('current_school_id');

        $turmas = Turma::where('school_id', $schoolId)
            ->orderBy('serie_turma')
            ->orderBy('turno')
            ->get();

        return response()->streamDownload(function () use ($turmas) {
            // Cabeçalhos + BOM UTF-8 (evita 7Âº)
            header('Content-Type: text/csv; charset=UTF-8');
            echo "\xEF\xBB\xBF"; // BOM

            //echo "sep=;\n";

            $out = fopen('php://output', 'w');

            // Cabeçalho
            fputcsv($out, ['matricula', 'nome', 'turma_id', 'serie_turma', 'turno'], ';');

            $contador = 1;
            foreach ($turmas as $turma) {
                fputcsv($out, [
                    '2025' . str_pad($contador, 4, '0', STR_PAD_LEFT), // exemplo de matrícula fictícia
                    "NOME COMPLETO DO ALUNO {$contador}",
                    $turma->id,
                    $turma->serie_turma,
                    $turma->turno,
                ], ';');

                $contador++;
            }

            fclose($out);

        }, 'modelo_alunos.csv');
    }

    /**
     * Recebe o CSV e gera a pré-visualização.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $schoolId = (int) session('current_school_id');

        $service = new CadastroLoteAlunoService($schoolId);
        $linhas  = $service->previewCSV($request->file('arquivo'));

        // Encodamos as linhas para mandar ao POST de importação
        $payload = base64_encode(json_encode($linhas));

        return view('escola.alunos_lote.preview', [
            'linhas'  => $linhas,
            'payload' => $payload,
        ]);
    }

    /**
     * Importa de fato as linhas já pré-visualizadas.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'linhas' => 'required',
        ]);

        $decoded = json_decode(base64_decode($request->linhas), true);

        if (!is_array($decoded)) {
            return redirect()
                ->route('escola.alunos.lote.index')
                ->with('error', 'Dados de importação inválidos. Reenvie o arquivo.');
        }

        $schoolId = (int) session('current_school_id');
        $service  = new CadastroLoteAlunoService($schoolId);

        $resultado = $service->importarLinhasValidadas($decoded);

        return view('escola.alunos_lote.resultado', compact('resultado'));
    }
}


/*
namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turma;
use App\Services\CadastroLoteAlunoService;

class CadastroLoteAlunoController extends Controller
{
    public function __construct()
    {
        // Já está protegido por:
        //  - auth
        //  - ensure.context
        //  - ensure.role.escola (no grupo de rotas)
        // aqui só garantimos school_id presente
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

        $turmas = Turma::where('school_id', $schoolId)
            ->orderBy('serie_turma')
            ->orderBy('turno')
            ->get();

        return view('escola.alunos_lote.index', compact('turmas'));
    }

    /**
     * Gera o modelo CSV com turmas da escola para o ano vigente.
     /
    public function modelo()
    {
        $schoolId = session('current_school_id');

        $turmas = Turma::where('school_id', $schoolId)
            ->orderBy('serie_turma')
            ->orderBy('turno')
            ->get();

        return response()->streamDownload(function () use ($turmas) {

            // Cabeçalhos + BOM UTF-8 (evita 7Âº)
            header('Content-Type: text/csv; charset=UTF-8');
            echo "\xEF\xBB\xBF"; // BOM

            echo "sep=;\n";

            $out = fopen('php://output', 'w');

            // Cabeçalho
            fputcsv($out, ['matricula', 'nome', 'turma_id', 'serie_turma', 'turno'], ';');

            $contador = 1;
            foreach ($turmas as $turma) {
                fputcsv($out, [
                    '2025' . str_pad($contador, 4, '0', STR_PAD_LEFT), // exemplo de matrícula fictícia
                    "NOME COMPLETO DO ALUNO {$contador}",
                    $turma->id,
                    $turma->serie_turma,
                    $turma->turno,
                ], ';');

                $contador++;
            }

            fclose($out);

        }, 'modelo_alunos.csv');
    }

    /**
     * Recebe o CSV e gera a pré-visualização.
     /
    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $schoolId = (int) session('current_school_id');

        $service = new CadastroLoteAlunoService($schoolId);
        $linhas  = $service->previewCSV($request->file('arquivo'));

        // Encodamos as linhas para mandar ao POST de importação
        $payload = base64_encode(json_encode($linhas));

        return view('escola.alunos_lote.preview', [
            'linhas'  => $linhas,
            'payload' => $payload,
        ]);
    }

    /**
     * Importa de fato as linhas já pré-visualizadas.
     /
    public function importar(Request $request)
    {
        $request->validate([
            'linhas' => 'required',
        ]);

        $decoded = json_decode(base64_decode($request->linhas), true);

        if (!is_array($decoded)) {
            return redirect()
                ->route('escola.alunos.lote.index')
                ->with('error', 'Dados de importação inválidos. Reenvie o arquivo.');
        }

        $schoolId = (int) session('current_school_id');
        $service  = new CadastroLoteAlunoService($schoolId);

        $resultado = $service->importarLinhasValidadas($decoded);

        return view('escola.alunos_lote.resultado', compact('resultado'));
    }
}
*/