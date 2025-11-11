<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use App\Models\Regimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegimentoController extends Controller
{
    /**
     * Exibe o regimento escolar atual e o formulário de upload.
     */
    public function index()
    {
        $regimento = Regimento::where('school_id', session('current_school_id'))->first();
        return view('escola.regimento.index', compact('regimento'));
    }

    /**
     * Atualiza (ou cria) o regimento escolar com upload de PDF.
     */
    public function update(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|mimes:pdf|max:4096', // até 4MB
        ]);

        $schoolId = session('current_school_id');
        $path = $request->file('arquivo')->store('regimentos', 'public');

        Regimento::updateOrCreate(
            ['school_id' => $schoolId],
            [
                'arquivo' => $path,
                'titulo' => 'Regimento Escolar - ' . date('Y'),
            ]
        );

        return back()->with('success', '📄 Regimento atualizado com sucesso!');
    }

    public function visualizar($schoolId)
    {
        $regimento = Regimento::where('school_id', $schoolId)->first();

        if (!$regimento) {
            return response('⚠️ Nenhum regimento cadastrado para esta escola.', 404);
        }

        if (!Storage::disk('public')->exists($regimento->arquivo)) {
            return response('⚠️ O arquivo do regimento não foi encontrado no servidor.', 404);
        }

        return response()->file(storage_path("app/public/{$regimento->arquivo}"));
    }

}
