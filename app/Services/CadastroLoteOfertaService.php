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
                'linha' => 0,
                'cpf' => '',
                'nome' => '',
                'disciplina_id' => '',
                'turma_id' => '',
                'status' => 'erro',
                'importavel' => false,
                'msg' => 'Não foi possível abrir o arquivo.'
            ]];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {

            $linhaNumero++;

            // Ignorar possíveis linhas sep=;
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Normalizar encoding
            $linhaBruta = implode(';', $dados);
            $linhaUtf8  = mb_convert_encoding($linhaBruta, 'UTF-8', 'UTF-8, ISO-8859-1');
            $dados      = str_getcsv($linhaUtf8, ';');

            // Garantir 6 colunas
            $dados = array_pad($dados, 6, '');

            [$cpf, $nome, $disciplinaId, $descr, $turmaId, $serieTurma] = $dados;

            $cpf          = trim($cpf);
            $nome         = trim($nome);
            $disciplinaId = trim($disciplinaId);
            $descr        = trim($descr);
            $turmaId      = trim($turmaId);
            $serieTurma   = trim($serieTurma);

            // Linha vazia?
            if ($this->linhaVazia($dados)) {
                continue;
            }

            // Validações simples
            if ($cpf === '' || !ctype_digit($cpf)) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId, 'erro', false,
                    'CPF inválido.');
                continue;
            }

            if ($disciplinaId === '' || !ctype_digit($disciplinaId)) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId, 'erro', false,
                    'disciplina_id inválido.');
                continue;
            }

            if ($turmaId === '' || !ctype_digit($turmaId)) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId, 'erro', false,
                    'turma_id inválido.');
                continue;
            }

            // Verifica turma
            $turma = Turma::where('id', $turmaId)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$turma) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId,
                    'erro', false, 'Turma não pertence à escola.');
                continue;
            }

            // Verifica disciplina
            $disciplina = Disciplina::where('id', $disciplinaId)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$disciplina) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId,
                    'erro', false, 'Disciplina não pertence à escola.');
                continue;
            }

            // Verifica professor pela tabela syrios_professor
            $professor = Professor::where('cpf', $cpf)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$professor) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId,
                    'erro', false, 'Professor não encontrado nesta escola (CPF: '.$cpf.').');
                continue;
            }

            // Verifica duplicidade de oferta
            $existe = Oferta::where('school_id', $this->schoolId)
                ->where('professor_id', $professor->id)
                ->where('disciplina_id', $disciplinaId)
                ->where('turma_id', $turmaId)
                ->exists();

            if ($existe) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId,
                    'aviso', false, 'Esta oferta já existe — não será importada.');
                continue;
            }

            // Linha válida
            $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, $disciplinaId, $turmaId,
                'ok', true, 'Linha válida — será importada.');
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

            if (empty($linha['importavel']) || $linha['status'] === 'erro') {
                $resultado[] = [
                    'linha' => $linha['linha'],
                    'cpf'   => $linha['cpf'],
                    'status'=> 'ignorado',
                    'msg'   => $linha['msg'] ?? 'Ignorado.'
                ];
                continue;
            }

            $cpf          = trim($linha['cpf']);
            $disciplinaId = (int) $linha['disciplina_id'];
            $turmaId      = (int) $linha['turma_id'];

            try {
                // Revalidar professor
                $professor = Professor::where('cpf', $cpf)
                    ->where('school_id', $this->schoolId)
                    ->first();

                if (!$professor) {
                    $resultado[] = [
                        'linha'  => $linha['linha'],
                        'cpf'    => $cpf,
                        'status' => 'erro',
                        'msg'    => 'Professor não encontrado no momento da importação.'
                    ];
                    continue;
                }

                // Revalidar disciplina
                if (!Disciplina::where('id', $disciplinaId)->where('school_id', $this->schoolId)->exists()) {
                    $resultado[] = [
                        'linha' => $linha['linha'],
                        'cpf'   => $cpf,
                        'status'=> 'erro',
                        'msg'   => 'Disciplina não encontrada.'
                    ];
                    continue;
                }

                // Revalidar turma
                if (!Turma::where('id', $turmaId)->where('school_id', $this->schoolId)->exists()) {
                    $resultado[] = [
                        'linha' => $linha['linha'],
                        'cpf'   => $cpf,
                        'status'=> 'erro',
                        'msg'   => 'Turma não encontrada.'
                    ];
                    continue;
                }

                // Verificar duplicidade antes de inserir
                $existe = Oferta::where('school_id', $this->schoolId)
                    ->where('professor_id', $professor->id)
                    ->where('disciplina_id', $disciplinaId)
                    ->where('turma_id', $turmaId)
                    ->exists();

                if ($existe) {
                    $resultado[] = [
                        'linha'  => $linha['linha'],
                        'cpf'    => $cpf,
                        'status' => 'aviso',
                        'msg'    => 'Oferta já existe — nada foi importado.'
                    ];
                    continue;
                }

                // Inserir oferta
                Oferta::create([
                    'school_id'     => $this->schoolId,
                    'professor_id'  => $professor->id,
                    'disciplina_id' => $disciplinaId,
                    'turma_id'      => $turmaId,
                ]);

                $resultado[] = [
                    'linha'  => $linha['linha'],
                    'cpf'    => $cpf,
                    'status' => 'sucesso',
                    'msg'    => 'Oferta adicionada com sucesso.'
                ];

            } catch (\Throwable $e) {
                $resultado[] = [
                    'linha' => $linha['linha'],
                    'cpf'   => $cpf,
                    'status'=> 'erro',
                    'msg'   => 'Erro inesperado: '.$e->getMessage(),
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

    private function linhaPreview(
        int $linha,
        string $cpf,
        string $nome,
        string $disciplinaId,
        string $turmaId,
        string $status,
        bool $importavel,
        string $msg
    ): array {
        return [
            'linha'         => $linha,
            'cpf'           => $cpf,
            'nome'          => $nome,
            'disciplina_id' => $disciplinaId,
            'turma_id'      => $turmaId,
            'status'        => $status,     // ok, aviso, erro
            'importavel'    => $importavel,
            'msg'           => $msg,
        ];
    }
}
