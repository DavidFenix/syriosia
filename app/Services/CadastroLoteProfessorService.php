<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\UsuarioRole;
use App\Models\Professor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CadastroLoteProfessorService
{
    private $schoolId;

    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
    }

    /**
     *  SANITIZAÇÃO COMPLETA DO CPF (Excel, Unicode, BOM, etc.)
     */
    private function sanitizarCpf($cpf)
    {
        if ($cpf === null) return '';

        // Converter encoding
        $cpf = mb_convert_encoding($cpf, 'UTF-8', 'UTF-8, ISO-8859-1, ASCII');

        // Remover BOM e caracteres invisíveis (zero-width, nbsp, etc.)
        $cpf = preg_replace('/\x{FEFF}|\x{200B}|\x{200C}|\x{200D}|\x{00A0}/u', '', $cpf);

        // Remover tudo que não seja A-Z, a-z ou 0-9
        $cpf = preg_replace('/[^A-Za-z0-9]/', '', $cpf);

        return trim($cpf);
    }

    /**
     *  SANITIZAÇÃO DO NOME
     */
    private function sanitizarNome($nome)
    {
        if ($nome === null) return '';

        $nome = mb_convert_encoding($nome, 'UTF-8', 'UTF-8, ISO-8859-1, ASCII');
        $nome = preg_replace('/\x{FEFF}|\x{200B}|\x{200C}|\x{200D}|\x{00A0}/u', '', $nome);

        return trim($nome);
    }

    /**
     *  DETECTA LINHA TOTALMENTE VAZIA
     */
    private function linhaVazia($dados)
    {
        foreach ($dados as $campo) {
            if (trim($campo) !== '') return false;
        }
        return true;
    }

    /**
     * --------------------------------------------------------------------
     *  PREVIEW DO CSV — 100% REGRAS DO SYRIOS
     * --------------------------------------------------------------------
     */
    public function previewCSV($file)
    {
        $preview = [];
        $cpfsArquivo = [];
        $linhaNumero = 0;

        $handle = fopen($file->getRealPath(), 'r');

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {

            $linhaNumero++;

            // Ignorar sep=;
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            if ($this->linhaVazia($dados)) continue;

            // Garante 3 colunas
            $dados = array_pad($dados, 3, '');

            list($cpfRaw, $nomeRaw, $roleRaw) = $dados;

            $cpf  = $this->sanitizarCpf($cpfRaw);
            $nome = $this->sanitizarNome($nomeRaw);
            $role = strtolower(trim($roleRaw));

            // Duplicado no arquivo
            if (in_array($cpf, $cpfsArquivo)) {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, 'erro', false, "CPF duplicado no arquivo");
                continue;
            }
            $cpfsArquivo[] = $cpf;

            // Campos vazios
            if ($cpf === '') {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, 'erro', false, "CPF vazio");
                continue;
            }

            if ($nome === '') {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, 'erro', false, "Nome vazio");
                continue;
            }

            if ($role !== 'professor') {
                $preview[] = $this->linhaPreview($linhaNumero, $cpf, $nome, 'ignorado', false, "Role '{$role}' não permitida");
                continue;
            }

            // Verificar se usuário existe
            $usuario = Usuario::where('cpf', $cpf)->first();

            if ($usuario) {

                // 🔍 Já é professor nesta escola?
                $jaProfessor = DB::table(prefix('professor'))
                    ->where('usuario_id', $usuario->id)
                    ->where('school_id', $this->schoolId)
                    ->exists();

                if ($jaProfessor) {
                    $preview[] = $this->linhaPreview(
                        $linhaNumero, $cpf, $nome,
                        'ignorado', false,
                        "Usuário já está vinculado como professor nesta escola"
                    );
                    continue;
                }

                // 🔍 Já tem role professor nesta escola?
                $jaTemRole = DB::table(prefix('usuario_role'))
                    ->where('usuario_id', $usuario->id)
                    ->where('school_id', $this->schoolId)
                    ->where('role_id', 4)
                    ->exists();

                if ($jaTemRole) {
                    $preview[] = $this->linhaPreview(
                        $linhaNumero, $cpf, $nome,
                        'ignorado', false,
                        "Usuário já possui a role professor nesta escola"
                    );
                    continue;
                }

                // 🔍 Existe em outra escola → OK para vincular
                if ($usuario->school_id != $this->schoolId) {
                    $preview[] = $this->linhaPreview(
                        $linhaNumero, $cpf, $nome,
                        'info', true,
                        "Usuário existe em outra escola — será vinculado"
                    );
                    continue;
                }

                // 🔍 Existe na mesma escola → erro
                $preview[] = $this->linhaPreview(
                    $linhaNumero, $cpf, $nome,
                    'erro', false,
                    "Usuário já existe nesta escola"
                );
                continue;
            }

            // Linha válida (criar + vincular)
            $preview[] = $this->linhaPreview(
                $linhaNumero, $cpf, $nome,
                'ok', true,
                "Será criado e vinculado"
            );
        }

        fclose($handle);

        return $preview;
    }

    /**
     *  FORMATAÇÃO PADRÃO DE LINHA DO PREVIEW
     */
    private function linhaPreview($linha, $cpf, $nome, $status, $importar, $msg)
    {
        return [
            'linha'    => $linha,
            'cpf'      => $cpf,
            'nome'     => $nome,
            'status'   => $status,
            'importar' => $importar,
            'msg'      => $msg
        ];
    }

    /**
     * --------------------------------------------------------------------
     *  IMPORTAÇÃO FINAL — SEGUINDO AS MESMAS REGRAS DO PREVIEW
     * --------------------------------------------------------------------
     */
    public function importarLinhasValidadas($linhas)
    {
        $resultados = [];
        $roleProfessorId = 4;

        foreach ($linhas as $linha) {

            if (!$linha['importar']) {
                $resultados[] = [
                    'status' => 'ignorado',
                    'msg' => $linha['msg']
                ];
                continue;
            }

            $cpf  = $this->sanitizarCpf($linha['cpf']);
            $nome = $this->sanitizarNome($linha['nome']);

            $usuario = Usuario::where('cpf', $cpf)->first();

            if ($usuario) {

                // Já é professor?
                $jaProfessor = Professor::where('usuario_id', $usuario->id)
                    ->where('school_id', $this->schoolId)
                    ->exists();

                if ($jaProfessor) {
                    $resultados[] = [
                        'status' => 'sucesso',
                        'msg'    => "Já era professor nesta escola (nenhuma ação necessária)"
                    ];
                    continue;
                }

                // Já tem role professor?
                $jaTemRole = UsuarioRole::where('usuario_id', $usuario->id)
                    ->where('school_id', $this->schoolId)
                    ->where('role_id', $roleProfessorId)
                    ->exists();

                if ($jaTemRole) {
                    $resultados[] = [
                        'status' => 'sucesso',
                        'msg'    => "Já possuía role professor nesta escola (nenhuma ação necessária)"
                    ];
                    continue;
                }

                // Vincular role + professor
                UsuarioRole::firstOrCreate([
                    'usuario_id' => $usuario->id,
                    'role_id'    => $roleProfessorId,
                    'school_id'  => $this->schoolId,
                ]);

                Professor::firstOrCreate([
                    'usuario_id' => $usuario->id,
                    'school_id'  => $this->schoolId,
                ]);

                $resultados[] = [
                    'status' => 'sucesso',
                    'msg'    => "Usuário já existia em outra escola + vinculado como professor"
                ];
                continue;
            }

            /**
             *  Criar novo usuário
             */
            $usuario = Usuario::create([
                'cpf'        => $cpf,
                'nome_u'     => $nome,
                'school_id'  => $this->schoolId,
                'senha_hash' => Hash::make($cpf),
                'status'     => 1,
            ]);

            // Criar role
            UsuarioRole::create([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleProfessorId,
                'school_id'  => $this->schoolId,
            ]);

            // Criar professor
            Professor::create([
                'usuario_id' => $usuario->id,
                'school_id'  => $this->schoolId,
            ]);

            $resultados[] = [
                'status' => 'sucesso',
                'msg'    => "Usuário criado + vinculado como professor"
            ];
        }

        return $resultados;
    }
}
