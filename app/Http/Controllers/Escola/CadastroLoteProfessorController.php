<?php

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
        return app(CadastroLoteProfessorService::class)->gerarModeloCSV();
    }

    public function preview(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $service = app(CadastroLoteProfessorService::class);
        $preview = $service->previewCSV($request->file('arquivo'));

        return view('escola.professores_lote.preview', compact('preview'));
    }

    public function importarConfirmado(Request $request)
    {
        $dados = json_decode($request->input('dados'), true);

        $service = app(CadastroLoteProfessorService::class);
        $resultado = $service->importarLinhasValidadas($dados);

        return view('escola.professores_lote.resultado_final', compact('resultado'));
    }
}
