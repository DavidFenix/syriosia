<?php 

namespace App\Services;

use App\Models\Aluno;
use App\Models\Turma;
use App\Models\Enturmacao;
use Illuminate\Http\UploadedFile;

class CadastroLoteAlunoService
{
    protected int $schoolId;
    protected int $anoLetivo;

    public function __construct(int $schoolId, ?int $anoLetivo = null)
    {
        $this->schoolId  = $schoolId;
        $this->anoLetivo = $anoLetivo ?? (int) date('Y');
    }

    /**
     * Gera o array de pré-visualização das linhas do CSV.
     *
     * Agora:
     * - Detecta aluno existente em QUALQUER escola.
     * - Mostra mensagens já antecipando se será criado, vinculado,
     *   apenas enturmado, ou movido de turma.
     */
    public function previewCSV(UploadedFile $file): array
    {
        $preview           = [];
        $matriculasArquivo = [];
        $linhaNumero       = 0;

        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return [[
                'linha'       => 0,
                'matricula'   => '',
                'nome'        => '',
                'turma_id'    => '',
                'serie_turma' => '',
                'turno'       => '',
                'status'      => 'erro',
                'importavel'  => false,
                'msg'         => 'Não foi possível abrir o arquivo.'
            ]];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {
            $linhaNumero++;

            // Ignorar linha "sep=;"
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Normalizar possíveis problemas de encoding (acentos)
            $linhaBruta = implode(';', $dados);
            $linhaUtf8  = mb_convert_encoding($linhaBruta, 'UTF-8', 'UTF-8, ISO-8859-1');
            $dados      = str_getcsv($linhaUtf8, ';');

            // Ignorar linha vazia
            if ($this->linhaVazia($dados)) {
                continue;
            }

            // Garantir 5 colunas: matricula, nome, turma_id, serie_turma, turno
            $dados = array_pad($dados, 5, '');

            [$matricula, $nome, $turmaId, $serieTurma, $turno] = $dados;

            $matricula  = trim($matricula);
            $nome       = trim($nome);
            $turmaId    = trim($turmaId);
            $serieTurma = trim($serieTurma);
            $turno      = trim($turno);

            // Duplicata de matrícula no próprio arquivo
            if ($matricula !== '' && in_array($matricula, $matriculasArquivo, true)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $matricula,
                    $nome,
                    $turmaId,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Matrícula duplicada no arquivo.'
                );
                continue;
            }
            if ($matricula !== '') {
                $matriculasArquivo[] = $matricula;
            }

            // Validações básicas
            if ($matricula === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $matricula,
                    $nome,
                    $turmaId,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Matrícula vazia.'
                );
                continue;
            }

            if ($nome === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $matricula,
                    $nome,
                    $turmaId,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Nome do aluno vazio.'
                );
                continue;
            }

            if ($turmaId === '' || !ctype_digit($turmaId)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $matricula,
                    $nome,
                    $turmaId,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Código da turma inválido.'
                );
                continue;
            }

            // Verifica se a turma pertence à escola atual
            $turma = Turma::where('id', (int) $turmaId)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$turma) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero,
                    $matricula,
                    $nome,
                    $turmaId,
                    $serieTurma,
                    $turno,
                    'erro',
                    false,
                    'Turma não encontrada ou não pertence a esta escola.'
                );
                continue;
            }

            /**
             * 🧠 A partir daqui: simula o que vai acontecer na importação.
             *
             * - Busca aluno pela matrícula (QUALQUER escola).
             * - Descobre se é nativo desta escola ou de outra.
             * - Checa enturmação (school_id atual + ano_letivo atual).
             * - Ajusta mensagem e status (ok/aviso) de acordo.
             */
            $status     = 'ok';
            $importavel = true;
            $mensagem   = 'Linha válida — será processada na importação.';

            $aluno = Aluno::where('matricula', $matricula)->first();

            if (!$aluno) {
                // Aluno ainda não existe no sistema: será criado nesta escola
                $mensagem = 'Aluno novo; será criado nesta escola e enturmado na turma ' .
                    $turma->serie_turma . ' (' . $turma->turno . ').';
            } else {
                $alunoNativoDestaEscola = ((int) $aluno->school_id === $this->schoolId);
                $alunoDeOutraEscola     = !$alunoNativoDestaEscola;

                // Enturmação nesta escola + ano vigente (modo simples)
                $enturmacao = Enturmacao::where('school_id', $this->schoolId)
                    ->where('aluno_id', $aluno->id)
                    ->where('ano_letivo', $this->anoLetivo)
                    ->first();

                if (!$enturmacao) {
                    // Nunca enturmado nesta escola/ano
                    if ($alunoNativoDestaEscola) {
                        $mensagem = 'Aluno já existe nesta escola; será apenas enturmado na turma ' .
                            $turma->serie_turma . ' (' . $turma->turno . ').';
                    } else {
                        $mensagem = 'Aluno já existe em outra escola; será vinculado e enturmado na turma ' .
                            $turma->serie_turma . ' (' . $turma->turno . ').';
                    }
                } else {
                    // Já tem enturmação neste ano nesta escola
                    if ((int) $enturmacao->turma_id === (int) $turma->id) {
                        $status   = 'aviso';
                        $mensagem = 'Aluno já está enturmado nesta mesma turma para o ano ' .
                            $this->anoLetivo . '. Nenhuma mudança será feita.';
                    } else {
                        $status      = 'aviso';
                        $turmaAntiga = Turma::find($enturmacao->turma_id);

                        if ($turmaAntiga) {
                            $mensagem = 'Aluno já está enturmado em ' .
                                $turmaAntiga->serie_turma . ' (' . $turmaAntiga->turno . ') neste ano; ' .
                                'será movido para ' . $turma->serie_turma . ' (' . $turma->turno . ').';
                        } else {
                            $mensagem = 'Aluno já possui enturmação neste ano; ' .
                                'será movido para a turma ' . $turma->serie_turma . ' (' . $turma->turno . ').';
                        }
                    }
                }
            }

            // Linha válida (mesmo que status=aviso) → importável
            $preview[] = $this->linhaPreview(
                $linhaNumero,
                $matricula,
                $nome,
                $turmaId,
                $serieTurma,
                $turno,
                $status,
                $importavel,
                $mensagem
            );
        }

        fclose($handle);

        return $preview;
    }

    /**
     * Importa efetivamente as linhas já validadas no preview.
     * Aplica a matriz de regras do "Modo Simples" + vínculo entre escolas.
     *
     * Regras principais:
     * - Se matrícula não existe em lugar nenhum → cria aluno na escola atual.
     * - Se matrícula já existe em outra escola → NÃO recria; reutiliza o mesmo aluno,
     *   apenas cria / atualiza enturmação para a escola atual.
     * - Se já enturmado no ano vigente, mesma turma → apenas avisa.
     * - Se já enturmado no ano vigente, outra turma → move de turma.
     */
    public function importarLinhasValidadas(array $linhas): array
    {
        $resultado = [];

        foreach ($linhas as $linha) {
            // Ignora o que não é importável ou já marcado como erro
            if (empty($linha['importavel']) || ($linha['status'] ?? '') === 'erro') {
                $resultado[] = [
                    'linha'     => $linha['linha'] ?? null,
                    'matricula' => $linha['matricula'] ?? '',
                    'nome'      => $linha['nome'] ?? '',
                    'status'    => 'ignorado',
                    'msg'       => $linha['msg'] ?? 'Linha ignorada (não importável).',
                ];
                continue;
            }

            $matricula = trim($linha['matricula'] ?? '');
            $nome      = trim($linha['nome'] ?? '');
            $turmaId   = (int) ($linha['turma_id'] ?? 0);

            try {
                // 1) Garante que a turma ainda existe e pertence à escola
                $turma = Turma::where('id', $turmaId)
                    ->where('school_id', $this->schoolId)
                    ->first();

                if (!$turma) {
                    $resultado[] = [
                        'linha'     => $linha['linha'] ?? null,
                        'matricula' => $matricula,
                        'nome'      => $nome,
                        'status'    => 'erro',
                        'msg'       => 'Turma não encontrada na escola no momento da importação.',
                    ];
                    continue;
                }

                /**
                 * 2) Procura aluno pela matrícula em TODO o sistema
                 *    (não só na escola atual).
                 */
                $aluno = Aluno::where('matricula', $matricula)->first();

                $foiCriadoAluno          = false;
                $alunoNativoDestaEscola  = false;
                $alunoDeOutraEscola      = false;

                if ($aluno) {
                    if ((int) $aluno->school_id === $this->schoolId) {
                        $alunoNativoDestaEscola = true;
                    } else {
                        $alunoDeOutraEscola = true;
                    }
                } else {
                    // Não existe em lugar nenhum → cria aluno nativo desta escola
                    $aluno = Aluno::create([
                        'matricula' => $matricula,
                        'school_id' => $this->schoolId,
                        'nome_a'    => $nome,
                    ]);
                    $foiCriadoAluno         = true;
                    $alunoNativoDestaEscola = true;
                }

                // 3) Enturmação no ano vigente (MODO SIMPLES) para a escola atual
                $enturmacao = Enturmacao::where('school_id', $this->schoolId)
                    ->where('aluno_id', $aluno->id)
                    ->where('ano_letivo', $this->anoLetivo)
                    ->first();

                // Nunca enturmado nesta escola/ano → cria
                if (!$enturmacao) {
                    Enturmacao::create([
                        'school_id'  => $this->schoolId,
                        'ano_letivo' => $this->anoLetivo,
                        'vigente'    => 1,
                        'aluno_id'   => $aluno->id,
                        'turma_id'   => $turmaId,
                    ]);

                    $msgBase = '';

                    if ($foiCriadoAluno) {
                        $msgBase = 'Aluno criado nesta escola e enturmado na turma ';
                    } elseif ($alunoNativoDestaEscola) {
                        $msgBase = 'Aluno já existia nesta escola e foi enturmado na turma ';
                    } elseif ($alunoDeOutraEscola) {
                        $msgBase = 'Aluno já existia em outra escola; foi vinculado e enturmado na turma ';
                    }

                    $resultado[] = [
                        'linha'     => $linha['linha'] ?? null,
                        'matricula' => $matricula,
                        'nome'      => $aluno->nome_a,
                        'status'    => 'sucesso',
                        'msg'       => $msgBase . $turma->serie_turma . ' (' . $turma->turno . ').',
                    ];
                    continue;
                }

                // Já enturmado neste ano → mesma turma?
                if ((int) $enturmacao->turma_id === $turmaId) {
                    $resultado[] = [
                        'linha'     => $linha['linha'] ?? null,
                        'matricula' => $matricula,
                        'nome'      => $aluno->nome_a,
                        'status'    => 'aviso',
                        'msg'       => 'Aluno já está enturmado nesta mesma turma para o ano ' .
                            $this->anoLetivo . '. Nenhuma alteração foi feita.',
                    ];
                    continue;
                }

                // Já enturmado neste ano em outra turma → troca de turma (modo simples)
                $turmaAntiga = Turma::find($enturmacao->turma_id);

                $enturmacao->turma_id = $turmaId;
                $enturmacao->save();

                $msg = 'Aluno já estava enturmado em outra turma neste ano. ';
                if ($turmaAntiga) {
                    $msg .= 'Movido de ' . $turmaAntiga->serie_turma . ' (' . $turmaAntiga->turno . ')';
                } else {
                    $msg .= 'Movido de uma turma anterior';
                }
                $msg .= ' para ' . $turma->serie_turma . ' (' . $turma->turno . ').';

                $resultado[] = [
                    'linha'     => $linha['linha'] ?? null,
                    'matricula' => $matricula,
                    'nome'      => $aluno->nome_a,
                    'status'    => 'sucesso',
                    'msg'       => $msg,
                ];

            } catch (\Throwable $e) {
                $resultado[] = [
                    'linha'     => $linha['linha'] ?? null,
                    'matricula' => $matricula,
                    'nome'      => $nome,
                    'status'    => 'erro',
                    'msg'       => 'Erro inesperado: ' . $e->getMessage(),
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
        string $matricula,
        string $nome,
        string $turmaId,
        string $serieTurma,
        string $turno,
        string $status,
        bool $importavel,
        string $msg
    ): array {
        return [
            'linha'       => $linha,
            'matricula'   => $matricula,
            'nome'        => $nome,
            'turma_id'    => $turmaId,
            'serie_turma' => $serieTurma,
            'turno'       => $turno,
            'status'      => $status,     // ok | erro | aviso
            'importavel'  => $importavel, // true|false
            'msg'         => $msg,
        ];
    }
}

/*
namespace App\Services;

use App\Models\Aluno;
use App\Models\Turma;
use App\Models\Enturmacao;
use Illuminate\Http\UploadedFile;

class CadastroLoteAlunoService
{
    protected int $schoolId;
    protected int $anoLetivo;

    public function __construct(int $schoolId, ?int $anoLetivo = null)
    {
        $this->schoolId  = $schoolId;
        $this->anoLetivo = $anoLetivo ?? (int) date('Y');
    }

    /**
     * ============================
     *  PRÉ-VISUALIZAÇÃO DO CSV
     * ============================
     /
    public function previewCSV(UploadedFile $file): array
    {
        $preview           = [];
        $matriculasArquivo = [];
        $linhaNumero       = 0;

        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return [[
                'linha'       => 0,
                'matricula'   => '',
                'nome'        => '',
                'turma_id'    => '',
                'serie_turma' => '',
                'turno'       => '',
                'status'      => 'erro',
                'importavel'  => false,
                'msg'         => 'Não foi possível abrir o arquivo.'
            ]];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {
            $linhaNumero++;

            // Ignora "sep=;"
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Normalizar acentuação
            $linhaBruta = implode(';', $dados);
            $linhaUtf8  = mb_convert_encoding($linhaBruta, 'UTF-8', 'UTF-8, ISO-8859-1');
            $dados      = str_getcsv($linhaUtf8, ';');

            if ($this->linhaVazia($dados)) continue;

            $dados = array_pad($dados, 5, '');

            [$matricula, $nome, $turmaId, $serieTurma, $turno] = $dados;

            $matricula  = trim($matricula);
            $nome       = trim($nome);
            $turmaId    = trim($turmaId);
            $serieTurma = trim($serieTurma);
            $turno      = trim($turno);

            // Duplicata dentro do arquivo
            if ($matricula !== '' && in_array($matricula, $matriculasArquivo, true)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $matricula, $nome,
                    $turmaId, $serieTurma, $turno,
                    'erro', false,
                    'Matrícula duplicada no arquivo.'
                );
                continue;
            }
            $matriculasArquivo[] = $matricula;

            // Validações básicas
            if ($matricula === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $matricula, $nome,
                    $turmaId, $serieTurma, $turno,
                    'erro', false,
                    'Matrícula vazia.'
                );
                continue;
            }

            if ($nome === '') {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $matricula, $nome,
                    $turmaId, $serieTurma, $turno,
                    'erro', false,
                    'Nome vazio.'
                );
                continue;
            }

            if ($turmaId === '' || !ctype_digit($turmaId)) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $matricula, $nome,
                    $turmaId, $serieTurma, $turno,
                    'erro', false,
                    'Código da turma inválido.'
                );
                continue;
            }

            // Turma válida?
            $turma = Turma::where('id', (int)$turmaId)
                ->where('school_id', $this->schoolId)
                ->first();

            if (!$turma) {
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $matricula, $nome,
                    $turmaId, $serieTurma, $turno,
                    'erro', false,
                    'Turma não encontrada nesta escola.'
                );
                continue;
            }

            // ================================
            // 🔥 ANALISAR O QUE ACONTECERIA NA IMPORTAÇÃO
            // ================================
            $analise = $this->analisarLinhaParaPreview($matricula, (int)$turmaId);

            $preview[] = $this->linhaPreview(
                $linhaNumero,
                $matricula,
                $nome,
                $turmaId,
                $serieTurma,
                $turno,
                $analise['status'],                // ok | erro | aviso
                $analise['status'] === 'ok',       // importável só se ok
                $analise['msg']
            );
        }

        fclose($handle);

        return $preview;
    }


    /**
     * ========================================================
     * 🔎 ANALISA UMA LINHA COMO SE FOSSE IMPORTAR (SEM SALVAR)
     * ========================================================
     /
    private function analisarLinhaParaPreview(string $matricula, int $turmaId): array
    {
        // Turma válida?
        $turma = Turma::where('id', $turmaId)
            ->where('school_id', $this->schoolId)
            ->first();

        if (!$turma) {
            return ['status' => 'erro', 'msg' => 'Turma não encontrada nesta escola.'];
        }

        // Aluno existe?
        $aluno = Aluno::where('matricula', $matricula)
            ->where('school_id', $this->schoolId)
            ->first();

        if (!$aluno) {
            return [
                'status' => 'ok',
                'msg'    => 'Aluno não existe — será criado e enturmado.'
            ];
        }

        // Enturmação deste ano
        $ent = Enturmacao::where('school_id', $this->schoolId)
            ->where('aluno_id', $aluno->id)
            ->where('ano_letivo', $this->anoLetivo)
            ->first();

        if (!$ent) {
            return [
                'status' => 'ok',
                'msg'    => 'Aluno já existe — será enturmado pela primeira vez neste ano.'
            ];
        }

        // Já enturmado na mesma turma
        if ($ent->turma_id == $turmaId) {
            return [
                'status' => 'aviso',
                'msg'    => 'Aluno já está enturmado nesta mesma turma.'
            ];
        }

        // Enturmado em outra turma → este é o caso de troca
        $turmaAntiga = Turma::find($ent->turma_id);

        if ($turmaAntiga) {
            return [
                'status' => 'aviso',
                'msg'    => "Aluno já está enturmado em {$turmaAntiga->serie_turma} ({$turmaAntiga->turno}) — será movido para {$turma->serie_turma} ({$turma->turno})."
            ];
        }

        return [
            'status' => 'aviso',
            'msg'    => 'Aluno já enturmado em outra turma — será movido.'
        ];
    }


    /**
     * ========================================================
     *  IMPORTAÇÃO REAL (sem alterações — já funciona bem)
     * ========================================================
     /
    public function importarLinhasValidadas(array $linhas): array
    {
        // <-- mantém tudo exatamente como sua versão
        // (nenhuma alteração necessária)
        // ...
        // (NÃO REPITO AQUI PORQUE VOCÊ JÁ TEM ESSA PARTE PRONTA)
        // ...
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
        string $matricula,
        string $nome,
        string $turmaId,
        string $serieTurma,
        string $turno,
        string $status,
        bool $importavel,
        string $msg
    ): array {
        return [
            'linha'       => $linha,
            'matricula'   => $matricula,
            'nome'        => $nome,
            'turma_id'    => $turmaId,
            'serie_turma' => $serieTurma,
            'turno'       => $turno,
            'status'      => $status,
            'importavel'  => $importavel,
            'msg'         => $msg,
        ];
    }
}
*/