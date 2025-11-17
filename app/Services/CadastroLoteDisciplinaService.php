<?php

namespace App\Services;

use App\Models\Disciplina;
use Illuminate\Http\UploadedFile;

class CadastroLoteDisciplinaService
{
    protected int $schoolId;

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    /**
     * Pré-visualização do CSV.
     */
    public function previewCSV(UploadedFile $file): array
    {
        $preview = [];
        $siglasArquivo = [];
        $linhaNumero = 0;

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return [[
                'linha' => 0,
                'abr'   => '',
                'descr_d' => '',
                'status' => 'erro',
                'importavel' => false,
                'msg' => 'Não foi possível abrir o arquivo.'
            ]];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {
            $linhaNumero++;

            // Ignorar linha "sep=;"
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Normaliza encoding
            $linhaBruta = implode(';', $dados);
            $linhaUtf8  = mb_convert_encoding($linhaBruta, 'UTF-8', 'UTF-8, ISO-8859-1');
            $dados      = str_getcsv($linhaUtf8, ';');

            if ($this->linhaVazia($dados)) continue;

            // Garante 2 colunas: abr / descr_d
            $dados = array_pad($dados, 2, '');

            [$abr, $descr] = $dados;
            $abr   = trim($abr);
            $descr = trim($descr);

            // Se detectar cabeçalhos do modelo — manter no preview como aviso
            if (
                strtolower($abr ?? '') === 'abr' ||
                strtolower($descr ?? '') === 'descr_d'
            ) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $abr, $descr,
                    'aviso', false, 'Linha de cabeçalho detectada — será ignorada.'
                );
                continue;
            }

            // // Ignorar o cabeçalho se existir
            // if ($linhaNumero === 1 && strtolower($abr) === 'abr' && strtolower($descr) === 'descr_d') {
            //     continue;
            // }

            // Sigla duplicada no arquivo
            if ($abr !== '' && in_array(strtolower($abr), $siglasArquivo)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $abr, $descr,
                    'erro', false, 'Sigla duplicada no arquivo.'
                );
                continue;
            }
            if ($abr !== '') {
                $siglasArquivo[] = strtolower($abr);
            }

            // Validações
            if ($abr === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $abr, $descr,
                    'erro', false, 'Campo "abr" vazio.'
                );
                continue;
            }

            if ($descr === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $abr, $descr,
                    'erro', false, 'Campo "descr_d" vazio.'
                );
                continue;
            }

            // Já existe disciplina na escola?
            $disciplina = Disciplina::where('school_id', $this->schoolId)
                ->where('abr', $abr)
                ->first();

            if ($disciplina) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $abr, $descr,
                    'aviso', false, 'Disciplina já existe — será ignorada.'
                );
                continue;
            }

            // Tudo OK
            $preview[] = $this->linhaPreview(
                $linhaNumero, $abr, $descr,
                'ok', true, 'Linha válida — será importada.'
            );
        }

        fclose($handle);
        return $preview;
    }

    /**
     * Importa somente as linhas marcadas como importáveis.
     */
    public function importar(array $linhas): array
    {
        $resultado = [];

        foreach ($linhas as $l) {
            if (($l['status'] ?? '') === 'erro' || empty($l['importavel'])) {
                // Ignorado
                $resultado[] = [
                    'linha' => $l['linha'],
                    'abr'   => $l['abr'],
                    'descr_d' => $l['descr_d'],
                    'status' => 'ignorado',
                    'msg'    => $l['msg']
                ];
                continue;
            }

            try {
                Disciplina::create([
                    'school_id' => $this->schoolId,
                    'abr'       => $l['abr'],
                    'descr_d'   => $l['descr_d']
                ]);

                $resultado[] = [
                    'linha' => $l['linha'],
                    'abr'   => $l['abr'],
                    'descr_d' => $l['descr_d'],
                    'status' => 'sucesso',
                    'msg'    => 'Disciplina criada com sucesso.'
                ];

            } catch (\Throwable $e) {
                $resultado[] = [
                    'linha' => $l['linha'],
                    'abr'   => $l['abr'],
                    'descr_d' => $l['descr_d'],
                    'status' => 'erro',
                    'msg'    => 'Erro inesperado: '.$e->getMessage()
                ];
            }
        }

        return $resultado;
    }

    private function linhaVazia(array $dados): bool
    {
        foreach ($dados as $d) {
            if (trim($d) !== '') return false;
        }
        return true;
    }

    private function linhaPreview(int $linha, string $abr, string $descr, string $status, bool $importavel, string $msg): array
    {
        return [
            'linha'      => $linha,
            'abr'        => $abr,
            'descr_d'    => $descr,
            'status'     => $status,
            'importavel' => $importavel,
            'msg'        => $msg,
        ];
    }
}
