<?php

namespace App\Services;

use App\Models\Turma;
use App\Models\Professor;
use App\Models\Disciplina;
use App\Models\Oferta;
use Illuminate\Http\UploadedFile;

class CadastroLoteOfertaService
{
    protected int $schoolId;

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    /*
    |--------------------------------------------------------------------------
    | 1) Preview do CSV
    |--------------------------------------------------------------------------
    | CSV esperado:
    | cpf_professor ; nome_professor ; disciplina_id ; descr_d ; turma_id ; serie_turma
    |--------------------------------------------------------------------------
    */
    public function previewCSV(UploadedFile $file): array
    {
        $preview = [];
        $linhaNumero = 0;

        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return [[
                'linha'         => 0,
                'cpf'           => '',
                'nome'          => '',
                'disciplina_id' => '',
                'descr_d'       => '',
                'turma_id'      => '',
                'serie_turma'   => '',
                'status'        => 'erro',
                'importavel'    => false,
                'msg'           => 'Não foi possível abrir o arquivo.'
            ]];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {
            $linhaNumero++;

            // Ignora linha "sep=;"
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Normaliza encoding
            $linhaBruta = implode(';', $dados);
            $linhaUtf8  = mb_convert_encoding($linhaBruta, 'UTF-8', 'UTF-8, ISO-8859-1');
            $dados      = str_getcsv($linhaUtf8, ';');

            // Linha totalmente vazia?
            if ($this->linhaVazia($dados)) {
                continue;
            }

            // Garante 6 colunas
            $dados = array_pad($dados, 6, '');

            [$cpf, $nome, $disciplinaId, $descr, $turmaId, $serieTurma] = $dados;

            $cpf          = trim($cpf);
            $nome         = trim($nome);
            $disciplinaId = trim($disciplinaId);
            $descr        = trim($descr);
            $turmaId      = trim($turmaId);
            $serieTurma   = trim($serieTurma);

            // Trata cabeçalho "cpf_professor;nome_professor;..."
            if (strtolower($cpf) === 'cpf_professor') {
                continue;
            }

            // Valores que iremos refinando com o que vem do banco
            $nomeFinal  = $nome;
            $descrFinal = $descr;
            $serieFinal = $serieTurma;

            // Validações simples
            //if ($cpf === '' || !ctype_digit($cpf)) {
            if ($cpf === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'erro', false, 'CPF inválido.'
                );
                continue;
            }

            if ($disciplinaId === '' || !ctype_digit($disciplinaId)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'erro', false, 'disciplina_id inválido.'
                );
                continue;
            }

            if ($turmaId === '' || !ctype_digit($turmaId)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'erro', false, 'turma_id inválido.'
                );
                continue;
            }

            // Verifica turma
            $turma = Turma::where('id', (int)$turmaId)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$turma) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'erro', false, 'Turma não pertence à escola.'
                );
                continue;
            }
            $serieFinal = $turma->serie_turma;

            // Verifica disciplina
            $disciplina = Disciplina::where('id', (int)$disciplinaId)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$disciplina) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'erro', false, 'Disciplina não pertence à escola.'
                );
                continue;
            }
            $descrFinal = $disciplina->descr_d;

            // Verifica professor pelo CPF via relação com usuário
            $professor = Professor::where('school_id', $this->schoolId)
                ->whereHas('usuario', function ($q) use ($cpf) {
                    $q->where('cpf', $cpf);
                })
                ->with('usuario')
                ->first();

            if (!$professor) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'erro', false,
                    'Professor não encontrado nesta escola (CPF: '.$cpf.').'
                );
                continue;
            }

            $nomeFinal = $professor->usuario->nome_u ?? $nomeFinal;

            // Verifica duplicidade de oferta
            $existe = Oferta::where('school_id', $this->schoolId)
                ->where('professor_id', $professor->id)
                ->where('disciplina_id', (int)$disciplinaId)
                ->where('turma_id', (int)$turmaId)
                ->exists();

            if ($existe) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                    $turmaId, $serieFinal, 'aviso', false,
                    'Esta oferta já existe — não será importada.'
                );
                continue;
            }

            // Linha OK
            $preview[] = $this->linhaPreview(
                $linhaNumero, $cpf, $nomeFinal, $disciplinaId, $descrFinal,
                $turmaId, $serieFinal, 'ok', true,
                'Linha válida — será importada.'
            );
        }

        fclose($handle);
        return $preview;
    }

    /*
    |--------------------------------------------------------------------------
    | 2) Importação
    |--------------------------------------------------------------------------
    */
    public function importar(array $linhas): array
    {
        $resultado = [];

        foreach ($linhas as $linha) {

            $linhaNum        = $linha['linha'] ?? null;
            $cpf             = trim($linha['cpf'] ?? '');
            $disciplinaId    = (int)($linha['disciplina_id'] ?? 0);
            $turmaId         = (int)($linha['turma_id'] ?? 0);
            $nomeLinha       = $linha['nome'] ?? '';
            $disciplinaLinha = $linha['descr_d'] ?? '';
            $turmaLinha      = $linha['serie_turma'] ?? '';

            // Se não é importável ou já marcado como erro no preview → ignorado
            if (empty($linha['importavel']) || ($linha['status'] ?? '') === 'erro') {
                $resultado[] = [
                    'linha'      => $linhaNum,
                    'cpf'        => $cpf,
                    'professor'  => $nomeLinha,
                    'disciplina' => $disciplinaLinha,
                    'turma'      => $turmaLinha,
                    'status'     => 'ignorado',
                    'msg'        => $linha['msg'] ?? 'Linha não marcada como importável (ignorada).',
                ];
                continue;
            }

            try {
                // Professor novamente (garantia)
                $professor = Professor::where('school_id', $this->schoolId)
                    ->whereHas('usuario', function ($q) use ($cpf) {
                        $q->where('cpf', $cpf);
                    })
                    ->with('usuario')
                    ->first();

                if (!$professor) {
                    $resultado[] = [
                        'linha'      => $linhaNum,
                        'cpf'        => $cpf,
                        'professor'  => $nomeLinha,
                        'disciplina' => $disciplinaLinha,
                        'turma'      => $turmaLinha,
                        'status'     => 'erro',
                        'msg'        => 'Professor não encontrado no momento da importação.',
                    ];
                    continue;
                }

                // Disciplina
                $disciplina = Disciplina::where('id', $disciplinaId)
                    ->where('school_id', $this->schoolId)
                    ->first();

                if (!$disciplina) {
                    $resultado[] = [
                        'linha'      => $linhaNum,
                        'cpf'        => $cpf,
                        'professor'  => $professor->usuario->nome_u ?? $nomeLinha,
                        'disciplina' => $disciplinaLinha,
                        'turma'      => $turmaLinha,
                        'status'     => 'erro',
                        'msg'        => 'Disciplina não encontrada.',
                    ];
                    continue;
                }

                // Turma
                $turma = Turma::where('id', $turmaId)
                    ->where('school_id', $this->schoolId)
                    ->first();

                if (!$turma) {
                    $resultado[] = [
                        'linha'      => $linhaNum,
                        'cpf'        => $cpf,
                        'professor'  => $professor->usuario->nome_u ?? $nomeLinha,
                        'disciplina' => $disciplina->descr_d ?? $disciplinaLinha,
                        'turma'      => $turmaLinha,
                        'status'     => 'erro',
                        'msg'        => 'Turma não encontrada.',
                    ];
                    continue;
                }

                // Duplicidade antes de inserir
                $existe = Oferta::where('school_id', $this->schoolId)
                    ->where('professor_id', $professor->id)
                    ->where('disciplina_id', $disciplinaId)
                    ->where('turma_id', $turmaId)
                    ->exists();

                if ($existe) {
                    $resultado[] = [
                        'linha'      => $linhaNum,
                        'cpf'        => $cpf,
                        'professor'  => $professor->usuario->nome_u ?? $nomeLinha,
                        'disciplina' => $disciplina->descr_d ?? $disciplinaLinha,
                        'turma'      => $turma->serie_turma ?? $turmaLinha,
                        'status'     => 'aviso',
                        'msg'        => 'Oferta já existia — nada foi inserido.',
                    ];
                    continue;
                }

                // Inserção
                Oferta::create([
                    'school_id'     => $this->schoolId,
                    'ano_letivo'    => date('Y'),  // ou session('ano_letivo') se existir
                    'vigente'       => 1,
                    'status'        => 1,

                    'professor_id'  => $professor->id,
                    'disciplina_id' => $disciplinaId,
                    'turma_id'      => $turmaId,
                ]);


                $resultado[] = [
                    'linha'      => $linhaNum,
                    'cpf'        => $cpf,
                    'professor'  => $professor->usuario->nome_u ?? $nomeLinha,
                    'disciplina' => $disciplina->descr_d ?? $disciplinaLinha,
                    'turma'      => $turma->serie_turma ?? $turmaLinha,
                    'status'     => 'sucesso',
                    'msg'        => 'Oferta adicionada com sucesso.',
                ];

            } catch (\Throwable $e) {
                $resultado[] = [
                    'linha'      => $linhaNum,
                    'cpf'        => $cpf,
                    'professor'  => $nomeLinha,
                    'disciplina' => $disciplinaLinha,
                    'turma'      => $turmaLinha,
                    'status'     => 'erro',
                    'msg'        => 'Erro inesperado: '.$e->getMessage(),
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
        string $cpf,
        string $nome,
        string $disciplinaId,
        string $descr,
        string $turmaId,
        string $serieTurma,
        string $status,
        bool $importavel,
        string $msg
    ): array {
        return [
            'linha'         => $linha,
            'cpf'           => $cpf,
            'nome'          => $nome,
            'disciplina_id' => $disciplinaId,
            'descr_d'       => $descr,
            'turma_id'      => $turmaId,
            'serie_turma'   => $serieTurma,
            'status'        => $status,     // ok | aviso | erro
            'importavel'    => $importavel,
            'msg'           => $msg,
        ];
    }
}
