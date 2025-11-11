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

        $nomeArquivo = "{$schoolId}_{$aluno->matricula}.png";
        $fotoUrl = Storage::exists("public/img-user/{$nomeArquivo}")
            ? asset("storage/img-user/{$nomeArquivo}")
            : asset('storage/img-user/padrao.png');

        return view('escola.alunos.foto', compact('aluno', 'fotoUrl'));
    }

    public function update(Request $request, $alunoId)
    {
        $aluno = Aluno::findOrFail($alunoId);
        $schoolId = session('current_school_id');

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:512', // 500KB
        ]);

        $nomeArquivo = "{$schoolId}_{$aluno->matricula}.png";

        // Apaga a antiga se existir
        Storage::delete("public/img-user/{$nomeArquivo}");

        // Armazena a nova
        $path = $request->file('foto')->storeAs('public/img-user', $nomeArquivo);

        return redirect()
            ->route('escola.alunos.index')
            ->with('success', '📸 Foto atualizada com sucesso!');
    }
}
