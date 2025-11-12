<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Role;
use App\Models\Professor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class CadastroLoteProfessorService
{
    /**
     * Gera o arquivo CSV modelo com formatação compatível com Excel.
     */
    public function gerarModeloCSV()
    {
        // BOM para UTF-8
        $bom = "\xEF\xBB\xBF";

        $csv  = $bom;
        $csv .= "sep=;\r\n"; // Excel oculta essa linha, mas usa o separador ;
        $csv .= "cpf;nome;role;disciplinas\r\n";
        $csv .= "00012345600;JOAO DA SILVA;professor;MAT,BIO\r\n";
        $csv .= "00098765400;MARIA OLIVEIRA;professor;\r\n";

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=modelo_professores.csv',
        ]);
    }


    /**
     * Lê o CSV e retorna apenas a pré-visualização
     * sem salvar nada no banco.
     */
    public function previewCSV($file)
    {
        $preview = [];
        $cpfsArquivo = [];
        $linhaNumero = 0;

        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return [
                ['linha' => 0, 'cpf' => '', 'nome' => '', 'status' => 'erro', 'msg' => 'Não foi possível abrir o arquivo.']
            ];
        }

        while (($dados = fgetcsv($handle, 0, ';')) !== false) {

            $linhaNumero++;

            // Ignorar linha sep=
            if ($linhaNumero === 1 && isset($dados[0]) && str_starts_with($dados[0], 'sep=')) {
                continue;
            }

            // Ignorar linha vazia
            if ($this->linhaVazia($dados)) continue;

            // Garantir 4 colunas
            $dados = array_pad($dados, 4, '');

            list($cpf, $nome, $role, $disciplinas) = $dados;

            $cpf = trim($cpf);
            $nome = trim($nome);
            $role = strtolower(trim($role));

            // Verificar CPF duplicado no próprio arquivo
            if (in_array($cpf, $cpfsArquivo)) {
                $preview[] = $this->previewLinha($linhaNumero, $cpf, $nome, 'erro', false, "CPF duplicado no arquivo");
                continue;
            }
            $cpfsArquivo[] = $cpf;

            // Validar campos
            if ($cpf === '') {
                $preview[] = $this->previewLinha($linhaNumero, $cpf, $nome, 'erro', false, "CPF vazio");
                continue;
            }

            if ($nome === '') {
                $preview[] = $this->previewLinha($linhaNumero, $cpf, $nome, 'erro', false, "Nome vazio");
                continue;
            }

            if ($role !== 'professor') {
                $preview[] = $this->previewLinha($linhaNumero, $cpf, $nome, 'ignorado', false, "Role '{$role}' não permitida");
                continue;
            }

            // Se chegou aqui → linha válida
            $preview[] = $this->previewLinha($linhaNumero, $cpf, $nome, 'ok', true, "Será importado");
        }

        fclose($handle);

        return $preview;
    }


    /**
     * Importa efetivamente os dados validados pelo preview.
     */
    public function importarLinhasValidadas($linhas)
    {
        $resultado = [];
        $schoolId = session('current_school_id');

        foreach ($linhas as $linha) {

            if (!$linha['importar']) {
                // Ignorar os que já foram marcados como erro/ignorado
                $resultado[] = [
                    'linha' => $linha['linha'],
                    'status' => 'ignorado',
                    'msg' => $linha['msg']
                ];
                continue;
            }

            $cpf = $linha['cpf'];
            $nome = $linha['nome'];

            // --- PROCESSAR ---
            $usuario = Usuario::where('cpf', $cpf)
                ->where('school_id', $schoolId)
                ->first();

            if (!$usuario) {
                // Criar novo
                $usuario = new Usuario();
                $usuario->school_id = $schoolId;
                $usuario->cpf = $cpf;
                $usuario->nome_u = $nome;
                $usuario->status = 1;
                $usuario->is_super_master = 0;
                $usuario->senha_hash = Hash::make($cpf);
                $usuario->save();

                $msg = "Usuário criado ({$cpf})";
            } else {
                $msg = "Usuário já existia";
            }

            // Garantir role professor
            $roleProfessor = Role::where('role_name', 'professor')->first();

            if ($roleProfessor) {
                $usuario->roles()->syncWithoutDetaching([
                    $roleProfessor->id => ['school_id' => $schoolId]
                ]);
            }

            // Garantir registro na tabela professor
            $prof = Professor::where('usuario_id', $usuario->id)
                ->where('school_id', $schoolId)
                ->first();

            if (!$prof) {
                $prof = new Professor();
                $prof->usuario_id = $usuario->id;
                $prof->school_id = $schoolId;
                $prof->save();

                $msg .= " + professor criado";
            } else {
                $msg .= " + professor já existia";
            }

            $resultado[] = [
                'linha' => $linha['linha'],
                'status' => 'sucesso',
                'msg' => $msg
            ];
        }

        return $resultado;
    }


    /* ===============================================================
       FUNÇÕES AUXILIARES
       =============================================================== */

    private function linhaVazia($dados)
    {
        foreach ($dados as $v) {
            if (trim($v) !== '') return false;
        }
        return true;
    }

    private function previewLinha($linha, $cpf, $nome, $status, $importar, $msg)
    {
        return [
            'linha'    => $linha,
            'cpf'      => $cpf,
            'nome'     => $nome,
            'status'   => $status,
            'importar' => $importar,
            'msg'      => $msg,
        ];
    }
}
