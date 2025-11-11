<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Aluno;

class LimparImagensCommand extends Command
{
    /**
     * O nome e assinatura do comando Artisan.
     */
    protected $signature = 'syrios:limpar-imagens {--delete : Remove as imagens órfãs após confirmação}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Verifica e (opcionalmente) remove imagens de alunos que não existem mais no sistema.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('🔍 Verificando imagens de alunos em storage/app/public/img-user/...');

        $arquivos = Storage::files('public/img-user');
        $total = count($arquivos);
        $this->info("Total de imagens encontradas: {$total}");

        $orfas = [];
        $validas = [];

        foreach ($arquivos as $path) {
            $nome = basename($path);
            if (!preg_match('/^(\d+)_(\d+)\.png$/', $nome, $m)) {
                $this->warn("❌ Nome inválido: {$nome}");
                $orfas[] = $nome;
                continue;
            }

            [$full, $schoolId, $matricula] = $m;

            $alunoExiste = Aluno::where('school_id', $schoolId)
                ->where('matricula', $matricula)
                ->exists();

            if ($alunoExiste) {
                $validas[] = $nome;
            } else {
                $orfas[] = $nome;
            }
        }

        $this->info("✅ Válidas: " . count($validas));
        $this->warn("⚠️ Órfãs: " . count($orfas));

        if (!empty($orfas)) {
            $this->table(['#', 'Arquivo órfão'], collect($orfas)->map(fn($a, $i) => [$i+1, $a])->toArray());

            if ($this->option('delete')) {
                if ($this->confirm('Tem certeza que deseja remover TODAS as imagens órfãs listadas acima?')) {
                    foreach ($orfas as $img) {
                        Storage::delete('public/img-user/' . $img);
                    }
                    $this->info('🧹 Imagens órfãs removidas com sucesso.');
                } else {
                    $this->info('Operação cancelada.');
                }
            }
        } else {
            $this->info('Nenhuma imagem órfã encontrada.');
        }

        return 0;
    }
}
