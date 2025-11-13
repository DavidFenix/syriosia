<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CadastroLoteProfessorService;

class CadastroLoteProfessorController extends Controller
{
    /**
     * 🔒 Proteção contextual no estilo oficial do Syrios
     * Apenas escolas podem cadastrar professores em lote,
     * e apenas usuários administrativos daquela escola têm permissão.
     */
    private function validarAcesso()
    {
        $schoolId  = session('current_school_id');
        $roleAtual = session('current_role'); 
        $auth      = auth()->user();

        // 1️⃣ Verifica se há escola selecionada
        if (!$schoolId) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Nenhuma escola selecionada no contexto.');
        }

        // 2️⃣ Carrega a escola atual
        $escola = \App\Models\Escola::find($schoolId);

        if (!$escola) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Escola não encontrada.');
        }

        // 3️⃣ Verifica se é uma escola genuína (filha de secretaria)
        if (is_null($escola->secretaria_id)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Somente escolas vinculadas a uma secretaria podem usar o cadastro em lote.');
        }

        // 4️⃣ Apenas role "escola" pode usar esta funcionalidade
        if ($roleAtual !== 'escola') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Ação permitida apenas para usuários com papel "escola".');
        }

        return null; // Tudo OK ✔
    }



    // ============================================================
    // 1) Tela inicial
    // ============================================================

    public function index()
    {
        if ($ret = $this->validarAcesso()) return $ret;

        return view('escola.professores_lote.index');
    }

    // ============================================================
    // 2) Download do modelo CSV
    // ============================================================

    public function modelo()
    {
        if ($ret = $this->validarAcesso()) return $ret;

        return response()->streamDownload(function () {
            echo "sep=;\ncpf;nome;role\ncpf1;nome1;professor\n";
        }, 'modelo_professores.csv');
    }

    // ============================================================
    // 3) Pré-visualização
    // ============================================================

    public function preview(Request $request)
    {
        if ($ret = $this->validarAcesso()) return $ret;

        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $schoolId = session('current_school_id');
        $service = new CadastroLoteProfessorService($schoolId);

        $linhas = $service->previewCSV($request->file('arquivo'));

        return view('escola.professores_lote.preview', compact('linhas'));
    }

    // ============================================================
    // 4) Importação final
    // ============================================================

    public function importar(Request $request)
    {
        if ($ret = $this->validarAcesso()) return $ret;

        $request->validate([
            'linhas' => 'required'
        ]);

        $linhas = json_decode(base64_decode($request->linhas), true);

        $schoolId = session('current_school_id');
        $service = new CadastroLoteProfessorService($schoolId);

        $resultado = $service->importarLinhasValidadas($linhas);

        return view('escola.professores_lote.resultado', compact('resultado'));
    }
}

/*
namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CadastroLoteProfessorService;

class CadastroLoteProfessorController extends Controller
{
    
    public function index()
    {
        return view('escola.professores_lote.index');
    }

    public function modelo()
    {
        return response()->streamDownload(function () {
            echo "sep=;\ncpf;nome;role\ncpf1;nome1;professor\n";
        }, 'modelo_professores.csv');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $schoolId = session('current_school_id');
        $service = new CadastroLoteProfessorService($schoolId);

        $linhas = $service->previewCSV($request->file('arquivo'));

        return view('escola.professores_lote.preview', compact('linhas'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'linhas' => 'required'
        ]);

        $linhas = json_decode(base64_decode($request->linhas), true);

        $schoolId = session('current_school_id');
        $service = new CadastroLoteProfessorService($schoolId);

        $resultado = $service->importarLinhasValidadas($linhas);

        return view('escola.professores_lote.resultado', compact('resultado'));
    }
}
*/