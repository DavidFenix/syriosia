<?php

namespace App\Services;

use App\Models\Turma;
use Illuminate\Http\UploadedFile;

class CadastroLoteTurmaService
{
    protected int $schoolId;

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    /*
    |----------------------------------------------------------------------
    | 1) Preview do CSV
    |----------------------------------------------------------------------
    | CSV esperado:
    |   serie_turma;turno
    |
    | Regras:
    | - Ignorar linha "sep=;" se existir.
    | - Ignorar linha de cabeçalho se vier exatamente "serie_turma;turno".
    | - Dentro do próprio arquivo:
    |     → 1ª ocorrência de (serie_turma + turno) é OK,
    |     → demais são AVISO "duplicada no arquivo".
    | - Se já existir turma na escola (mesma serie_turma + turno):
    |     → AVISO "já existe na escola".
    |----------------------------------------------------------------------
    */
    public function previewCSV(UploadedFile $file): array
    {
        $preview      = [];
        $linhaNumero  = 0;
        $chavesArquivo = []; // para detectar duplicadas no próprio arquivo

        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return [[
                'linha'       => 0,
                'serie_turma' => '',
                'turno'       => '',
                'status'      => 'erro',
                'importavel'  => false,
                'msg'         => 'Não foi possível abrir o arquivo.',
            ]];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {
            $linhaNumero++;

            // Ignorar "sep=;"
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Normalizar encoding
            $linhaBruta = implode(';', $dados);
            $linhaUtf8  = mb_convert_encoding($linhaBruta, 'UTF-8', 'UTF-8, ISO-8859-1');
            $dados      = str_getcsv($linhaUtf8, ';');

            // Garantir 2 colunas: serie_turma;turno
            $dados = array_pad($dados, 2, '');

            [$serieTurma, $turno] = $dados;

            $serieTurma = trim($serieTurma);
            $turno      = trim($turno);

            // Linha vazia?
            if ($this->linhaVazia($dados)) {
                continue;
            }

            // Detectar cabeçalho exato "serie_turma;turno"
            if (
                strtolower($serieTurma) === 'serie_turma' &&
                strtolower($turno) === 'turno'
            ) {
                // apenas ignorar essa linha
                continue;
            }

            // Validações básicas
            if ($serieTurma === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Campo serie_turma vazio.'
                );
                continue;
            }

            if ($turno === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Campo turno vazio.'
                );
                continue;
            }

            // Detectar duplicidade no próprio arquivo
            $chave = mb_strtolower($serieTurma.'|'.$turno, 'UTF-8');

            if (in_array($chave, $chavesArquivo, true)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $serieTurma,
                    $turno,
                    'aviso',
                    false,
                    'Turma duplicada no arquivo — apenas a primeira será importada.'
                );
                continue;
            }

            $chavesArquivo[] = $chave;

            // Verificar se já existe na escola
            $jaExiste = Turma::where('school_id', $this->schoolId)
                ->where('serie_turma', $serieTurma)
                ->where('turno', $turno)
                ->exists();

            if ($jaExiste) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $serieTurma,
                    $turno,
                    'aviso',
                    false,
                    'Turma já existe nesta escola — não será importada.'
                );
                continue;
            }

            // Linha OK
            $preview[] = $this->linhaPreview(
                $linhaNumero,
                $serieTurma,
                $turno,
                'ok',
                true,
                'Linha válida — turma será criada.'
            );
        }

        fclose($handle);

        return $preview;
    }

    /*
    |----------------------------------------------------------------------
    | 2) Importação efetiva
    |----------------------------------------------------------------------
    */
    public function importarLinhas(array $linhas): array
    {
        $resultado = [];

        foreach ($linhas as $linha) {

            // Ignorar linhas marcadas como não importáveis ou com erro
            if (empty($linha['importavel']) || ($linha['status'] ?? '') === 'erro') {
                $resultado[] = [
                    'linha'       => $linha['linha']       ?? null,
                    'serie_turma' => $linha['serie_turma'] ?? '',
                    'turno'       => $linha['turno']       ?? '',
                    'status'      => 'ignorado',
                    'msg'         => $linha['msg']         ?? 'Linha ignorada (não importável).',
                ];
                continue;
            }

            $serieTurma = trim($linha['serie_turma'] ?? '');
            $turno      = trim($linha['turno'] ?? '');

            if ($serieTurma === '' || $turno === '') {
                $resultado[] = [
                    'linha'       => $linha['linha'] ?? null,
                    'serie_turma' => $serieTurma,
                    'turno'       => $turno,
                    'status'      => 'erro',
                    'msg'         => 'Dados incompletos na linha (serie_turma/turno vazios).',
                ];
                continue;
            }

            try {
                // Revalida se turma já existe (garantia extra)
                $jaExiste = Turma::where('school_id', $this->schoolId)
                    ->where('serie_turma', $serieTurma)
                    ->where('turno', $turno)
                    ->exists();

                if ($jaExiste) {
                    $resultado[] = [
                        'linha'       => $linha['linha'] ?? null,
                        'serie_turma' => $serieTurma,
                        'turno'       => $turno,
                        'status'      => 'aviso',
                        'msg'         => 'Turma já existia na escola — nada foi criado.',
                    ];
                    continue;
                }

                // Cria turma
                Turma::create([
                    'school_id'   => $this->schoolId,
                    'serie_turma' => $serieTurma,
                    'turno'       => $turno,
                ]);

                $resultado[] = [
                    'linha'       => $linha['linha'] ?? null,
                    'serie_turma' => $serieTurma,
                    'turno'       => $turno,
                    'status'      => 'sucesso',
                    'msg'         => 'Turma criada com sucesso.',
                ];

            } catch (\Throwable $e) {
                $resultado[] = [
                    'linha'       => $linha['linha'] ?? null,
                    'serie_turma' => $serieTurma,
                    'turno'       => $turno,
                    'status'      => 'erro',
                    'msg'         => 'Erro inesperado: '.$e->getMessage(),
                ];
            }
        }

        return $resultado;
    }

    private function linhaVazia(array $dados): bool
    {
        foreach ($dados as $d) {
            if (trim($d) !== '') {
                return false;
            }
        }
        return true;
    }

    private function linhaPreview(
        int $linha,
        string $serieTurma,
        string $turno,
        string $status,
        bool $importavel,
        string $msg
    ): array {
        return [
            'linha'       => $linha,
            'serie_turma' => $serieTurma,
            'turno'       => $turno,
            'status'      => $status,     // ok | aviso | erro
            'importavel'  => $importavel, // true|false
            'msg'         => $msg,
        ];
    }
}
