<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turma;
use App\Services\CadastroLoteTurmaService;

class CadastroLoteTurmaController extends Controller
{
    public function __construct()
    {
        // Já está no grupo: auth + ensure.context + ensure.role.escola
        $this->middleware(function ($request, $next) {
            if (!session('current_school_id')) {
                return redirect()
                    ->route('choose.school')
                    ->with('error', 'Nenhuma escola selecionada no contexto.');
            }
            return $next($request);
        });
    }

    /**
     * Tela principal: explicação + upload.
     */
    public function index()
    {
        $schoolId = (int) session('current_school_id');

        // Apenas para exibir algumas turmas existentes (informativo)
        $turmas = Turma::where('school_id', $schoolId)
            ->orderBy('serie_turma')
            ->orderBy('turno')
            ->get();

        return view('escola.turmas_lote.index', compact('turmas'));
    }

    /**
     * Gera modelo CSV básico.
     */
    public function modelo()
    {
        return response()->streamDownload(function () {

            header('Content-Type: text/csv; charset=UTF-8');
            echo "\xEF\xBB\xBF"; // BOM UTF-8
            echo "sep=;\n";

            $out = fopen('php://output', 'w');

            // Cabeçalho
            fputcsv($out, ['serie_turma', 'turno'], ';');

            // Exemplo(s) de linha
            fputcsv($out, ['7º Ano A', 'INTEGRAL'], ';');

            fclose($out);

        }, 'modelo_turmas.csv');
    }

    /**
     * Recebe o CSV e gera o preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $schoolId = (int) session('current_school_id');

        $service = new CadastroLoteTurmaService($schoolId);
        $linhas  = $service->previewCSV($request->file('arquivo'));

        $payload = base64_encode(json_encode($linhas));

        return view('escola.turmas_lote.preview', [
            'linhas'  => $linhas,
            'payload' => $payload,
        ]);
    }

    /**
     * Importa as linhas previamente pré-visualizadas.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'linhas' => 'required',
        ]);

        $decoded = json_decode(base64_decode($request->linhas), true);

        if (!is_array($decoded)) {
            return redirect()
                ->route('escola.turmas.lote.index')
                ->with('error', 'Dados de importação inválidos. Reenvie o arquivo.');
        }

        $schoolId = (int) session('current_school_id');
        $service  = new CadastroLoteTurmaService($schoolId);

        $resultado = $service->importarLinhas($decoded);

        return view('escola.turmas_lote.resultado', compact('resultado'));
    }
}
