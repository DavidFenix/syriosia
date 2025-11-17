<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Disciplina;
use App\Services\CadastroLoteDisciplinaService;

class CadastroLoteDisciplinaController extends Controller
{
    public function index()
    {
        $schoolId = session('current_school_id');

        $disciplinas = Disciplina::where('school_id', $schoolId)
            ->orderBy('abr')
            ->get();

        return view('escola.disciplinas_lote.index', compact('disciplinas'));
    }

    public function modelo()
    {
        $schoolId = session('current_school_id');

        $disciplinas = Disciplina::where('school_id', $schoolId)
            ->orderBy('abr')
            ->get();

        return response()->streamDownload(function () use ($disciplinas) {

            header('Content-Type: text/csv; charset=UTF-8');
            echo "\xEF\xBB\xBF"; // BOM for Excel

            //echo "sep=;\n";

            $out = fopen('php://output', 'w');

            fputcsv($out, ['abr', 'descr_d'], ';');

            $i = 1;
            foreach ($disciplinas as $d) {
                fputcsv($out, [
                    $d->abr ?? "DISC{$i}",
                    $d->descr_d ?? "Disciplina {$i}"
                ], ';');

                $i++;
            }

            fclose($out);

        }, 'modelo_disciplinas.csv');
    }


    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt'
        ]);

        $schoolId = session('current_school_id');

        $service = new CadastroLoteDisciplinaService($schoolId);
        $linhas  = $service->previewCSV($request->file('arquivo'));

        $payload = base64_encode(json_encode($linhas));

        return view('escola.disciplinas_lote.preview', [
            'linhas'  => $linhas,
            'payload' => $payload,
        ]);
    }


    public function importar(Request $request)
    {
        $request->validate([
            'linhas' => 'required'
        ]);

        $dados = json_decode(base64_decode($request->linhas), true);

        if (!is_array($dados)) {
            return redirect()
                ->route('escola.disciplinas.lote.index')
                ->with('error', 'Dados inválidos. Envie novamente.');
        }

        $schoolId = session('current_school_id');

        $service = new CadastroLoteDisciplinaService($schoolId);
        $resultado = $service->importar($dados);

        return view('escola.disciplinas_lote.resultado', compact('resultado'));
    }
}
