<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Aluno;

class AlunoFotoLoteController extends Controller
{
    
    // ✅ 7️⃣ RESULTADO FINAL
    // Etapa                        Resultado
    // Envia vários arquivos        Cada um é verificado
    // Matrícula encontrada         Substitui ou cria nova foto
    // Matrícula não encontrada     Ignorado e listado
    // Erro técnico                 Reportado na seção de erros
    // Ao final                     Exibe relatório com três colunas coloridas
    public function index()
    {
        return view('escola.alunos.fotos_lote');
    }

    public function store(Request $request)
    {
        $schoolId = session('current_school_id');

        $request->validate([
            'fotos.*' => 'required|image|mimes:png,jpg,jpeg|max:512',
        ]);

        $resultados = [
            'sucesso' => [],
            'ignorado' => [],
            'erro' => [],
        ];

        foreach ($request->file('fotos') as $file) {
            try {
                // Extrai matrícula do nome do arquivo (ex.: 12345.png)
                $nomeOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $matricula = preg_replace('/[^0-9]/', '', $nomeOriginal);

                if (!$matricula) {
                    $resultados['ignorado'][] = "{$nomeOriginal}.png (sem matrícula)";
                    continue;
                }

                // Procura aluno na escola atual
                $aluno = Aluno::where('matricula', $matricula)
                    ->where('school_id', $schoolId)
                    ->first();

                if (!$aluno) {
                    $resultados['ignorado'][] = "{$matricula}.png (aluno não encontrado)";
                    continue;
                }

                // Nome final com prefixo da escola
                $nomeFinal = "{$schoolId}_{$matricula}.png";

                // Substitui se já existir
                Storage::delete("public/img-user/{$nomeFinal}");
                $file->storeAs('public/img-user', $nomeFinal);

                $resultados['sucesso'][] = "{$matricula}.png";
            } catch (\Throwable $e) {
                $resultados['erro'][] = $file->getClientOriginalName() . ' (' . $e->getMessage() . ')';
            }
        }

        return view('escola.alunos.fotos_lote_resultado', compact('resultados'));
    }
}
