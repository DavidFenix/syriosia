<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aluno;
use Illuminate\Support\Facades\Storage;

class AlunoFotoController extends Controller
{
    public function edit($alunoId)
    {
        $aluno = Aluno::findOrFail($alunoId);
        $schoolId = session('current_school_id');

        // URL final da foto usando nossa função universal
        $fotoUrl = syrios_user_photo($aluno->matricula, $schoolId);

        return view('escola.alunos.foto', compact('aluno', 'fotoUrl'));
    }

    public function update(Request $request, $alunoId)
    {
        $aluno = Aluno::findOrFail($alunoId);
        $schoolId = session('current_school_id');

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:512', // 500KB
        ]);

        // $nomeArquivo = "{$schoolId}_{$aluno->matricula}.png";

        // // Apaga a antiga se existir
        // Storage::delete("public/img-user/{$nomeArquivo}");

        // // Armazena a nova
        // $path = $request->file('foto')->storeAs('public/img-user', $nomeArquivo);

        // return redirect()
        //     ->route('escola.alunos.index')
        //     ->with('success', '📸 Foto atualizada com sucesso!');

        $matricula = $aluno->matricula;

        // Remove QUALQUER versão existente da foto (png/jpg/jpeg/webp)
        foreach (syrios_valid_extensions() as $ext) {
            $relative = "img-user/{$schoolId}_{$matricula}.{$ext}";
            $absolute = storage_syrios_path($relative);

            if (file_exists($absolute)) {
                @unlink($absolute);
            }
        }

        // Extensão real do upload
        $extNova = $request->file('foto')->getClientOriginalExtension();
        $nomeArquivo = "{$schoolId}_{$matricula}.{$extNova}";

        // Salva nova foto com o Storage (funciona em qualquer host)
        $request->file('foto')->storeAs('public/img-user', $nomeArquivo);

        return redirect()
            ->route('escola.alunos.index')
            ->with('success', '📸 Foto atualizada com sucesso!');


    }
}
