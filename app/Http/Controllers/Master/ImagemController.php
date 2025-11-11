<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Aluno;
use App\Models\Escola;

class ImagemController extends Controller
{
    // ✅ RESULTADO FINAL
    // Página /master/imagens com filtros avançados.
    // Resultados agrupados por escola.
    // Checkbox por escola e botão “Remover Selecionadas”.
    // Logs detalhados no laravel.log.
    // Ação segura e rastreável.
    public function index(Request $request)
    {
        $arquivos = Storage::files('public/img-user');
        $escolas = Escola::select('id', 'nome_e')->orderBy('nome_e')->get();

        $filtroEscola = $request->input('school_id');
        $filtroPasta = trim($request->input('pasta', ''));

        $orfas = [];
        $validas = [];

        foreach ($arquivos as $path) {
            if ($filtroPasta && !str_contains($path, $filtroPasta)) {
                continue;
            }

            $nome = basename($path);

            if (!preg_match('/^(\d+)_(\d+)\.png$/', $nome, $m)) {
                // Se não segue o padrão esperado, agrupa sob a chave "sem_id"
                $orfas['sem_id'][] = $nome;
                continue;
            }

            [$full, $schoolId, $matricula] = $m;

            // Aplica filtro por escola, se selecionado
            if ($filtroEscola && (int)$filtroEscola !== (int)$schoolId) {
                continue;
            }

            $alunoExiste = Aluno::where('school_id', $schoolId)
                ->where('matricula', $matricula)
                ->exists();

            if ($alunoExiste) {
                $validas[$schoolId][] = $nome;
            } else {
                $orfas[$schoolId][] = $nome;
            }
        }

        return view('master.imagens.index', compact(
            'escolas', 'validas', 'orfas', 'filtroEscola', 'filtroPasta'
        ));
    }

    public function limpar(Request $request)
    {
        $arquivos = $request->input('arquivos', []);

        foreach ($arquivos as $img) {
            Storage::delete('public/img-user/' . $img);
            \Log::info("🧹 Imagem órfã removida via painel master", [
                'arquivo' => $img,
                'user_id' => auth()->id(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        return back()->with('success', count($arquivos) . ' imagem(ns) removida(s) com sucesso.');
    }
}
