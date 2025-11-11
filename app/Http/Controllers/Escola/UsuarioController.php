<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\{Usuario, Role, Professor, Escola};

/**
 * Controller consolidado para Edição de Usuário no contexto da ESCOLA.
     *
     * \u26a0\ufe0f Princípios preservados (ver Model Set Context):
     * - Hierarquia de permissões: master → secretaria → escola → comuns.
     * - Regras por contexto da escola atual (session('current_school_id')).
     * - Self: só pode alterar a própria senha (não nome/status).
     * - Nativo da escola: pode alterar nome, senha e status.
     * - Vinculado (de outra escola): somente leitura (view-only).
     * - Usuário com role master/secretaria: sempre protegido (sem edição no contexto da escola).
     * - Gestor escolar (role "escola"): um gestor não pode editar outro gestor da mesma escola.
     * - Usuários externos (sem vínculo com a escola atual): bloqueados.
     * - Sem duplicar lógicas no Blade: o Controller decide o que é editável.
     */
class UsuarioController extends Controller
{
    public function index()
    {
        $schoolId = session('current_school_id');
        $usuarios = Usuario::whereHas('roles', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->get();

        return view('escola.usuarios.index', compact('usuarios'));
    }


    public function create()
    {
        $schoolId = session('current_school_id');

        // 🔒 Filtra roles permitidas (exclui master, secretaria, escola)
        $roles = Role::whereNotIn('role_name', ['master', 'secretaria', 'escola'])->get();

        return view('escola.usuarios.create', compact('roles', 'schoolId'));
    }

    /*🧱 Resumo das proteções
        Cenário / Ação
        Escola tenta criar usuário com role master, secretaria ou escola  /  ❌ Rejeitado com mensagem amigável
        Escola tenta vincular role proibida via POST (manual)  / ❌ Rejeitado
        Interface de criação (create) /  🔒 Já não mostra essas roles
        Inserções duplicadas   / ✅ Prevenidas com insertOrIgnore()
        Roles superiores existentes no usuário / ✅ Mantidas, não removidas
        Role professor / 👨‍🏫 Cria entrada em syrios_professor automaticamente
        */
    public function store(Request $request)
    {
        $schoolId = session('current_school_id'); // contexto da escola logada

        $request->validate([
            'nome_u'   => 'required|string|max:100',
            'cpf'      => 'required|string|max:11',
            'password' => 'required|string|min:6',
            'status'   => 'required|boolean',
            'roles'    => 'required|array'
        ]);

        // 🔒 Protege contra tentativa manual de criar usuários com roles proibidas
        $rolesInvalidas = Role::whereIn('id', $request->roles)
            ->whereIn('role_name', ['master', 'secretaria', 'escola'])
            ->pluck('role_name')
            ->toArray();

        if (!empty($rolesInvalidas)) {
            return back()
                ->withInput()
                ->with('error', '🚫 Não é permitido criar usuário com as roles: ' . implode(', ', $rolesInvalidas));
        }

        // 🔍 Verifica se já existe usuário com o mesmo CPF
        $usuarioExistente = Usuario::where('cpf', $request->cpf)->first();

        if ($usuarioExistente) {
            // Redireciona para vinculação
            return redirect()
                ->back()
                ->withInput()
                ->with('usuario_existente', $usuarioExistente->id);
        }

        // 👤 Cria novo usuário nesta escola
        $usuario = Usuario::create([
            'school_id'  => $schoolId,
            'cpf'        => $request->cpf,
            'senha_hash' => Hash::make($request->password),
            'nome_u'     => $request->nome_u,
            'status'     => $request->status,
        ]);

        // 🔗 Associa roles (apenas as permitidas)
        foreach ($request->roles as $roleId) {
            DB::table(prefix('usuario_role'))->insertOrIgnore([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleId,
                'school_id'  => $schoolId,
            ]);
        }

        // 👨‍🏫 Se for professor → cria também em syrios_professor
        $roleProfessorId = Role::where('role_name', 'professor')->value('id');
        if (in_array($roleProfessorId, $request->roles)) {
            Professor::firstOrCreate([
                'usuario_id' => $usuario->id,
                'school_id'  => $schoolId
            ]);
        }

        return redirect()->route('escola.usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function vincular(Request $request, Usuario $usuario)
    {
        $schoolId = session('current_school_id');

        if (!$schoolId) {
            return redirect()->route('escola.usuarios.index')
                ->with('error', 'Nenhuma escola selecionada no contexto.');
        }

        $request->validate([
            'roles' => 'required|array'
        ]);

        // 🔒 Bloqueia tentativa de vincular roles proibidas
        $rolesInvalidas = Role::whereIn('id', $request->roles)
            ->whereIn('role_name', ['master', 'secretaria', 'escola'])
            ->pluck('role_name')
            ->toArray();

        if (!empty($rolesInvalidas)) {
            return back()->with('error', '🚫 Não é permitido vincular as roles: ' . implode(', ', $rolesInvalidas));
        }

        // 🔍 Busca roles já existentes nesta escola
        $rolesExistentes = DB::table(prefix('usuario_role'))
            ->where('usuario_id', $usuario->id)
            ->where('school_id', $schoolId)
            ->pluck('role_id')
            ->toArray();

        // 🔎 Calcula apenas as novas roles (sem duplicar)
        $novasRoles = array_diff($request->roles, $rolesExistentes);

        foreach ($novasRoles as $roleId) {
            DB::table(prefix('usuario_role'))->insertOrIgnore([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleId,
                'school_id'  => $schoolId,
            ]);

            // 👨‍🏫 Se for professor → cria também em syrios_professor
            $roleProfessorId = Role::where('role_name', 'professor')->value('id');
            if ($roleId == $roleProfessorId) {
                Professor::firstOrCreate([
                    'usuario_id' => $usuario->id,
                    'school_id'  => $schoolId
                ]);
            }
        }

        return redirect()->route('escola.usuarios.index')
            ->with('success', 'Usuário vinculado à escola com sucesso!');
    }

    /*
    🧠 Resumo lógico
        Regra   Efeito
        🔒 Usuário master/secretaria intocável   Impede qualquer alteração de roles
        🧱 Somente roles da escola atual são modificadas Preserva vínculos com outras escolas
        👥 Gestor não edita outro gestor Segurança hierárquica local
        🙋 Gestor pode editar suas próprias roles (exceto remover sua role escola)   Autonomia controlada
        📋 Apenas roles permitidas (professor, aluno, pais...)   Coerência com contexto escolar
        */
    public function editRoles(Usuario $usuario)
    {
        $auth = auth()->user();
        $schoolId = session('current_school_id');
        $escolaAtual = Escola::find($schoolId);

        if (!$escolaAtual) {
            return redirect()->route('escola.dashboard')->with('error', 'Nenhuma escola selecionada.');
        }

        // 🧱 1️⃣ Protege contra acesso fora do escopo da escola
        $vinculadoAqui = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->exists();

        if ($usuario->school_id !== $schoolId && !$vinculadoAqui) {
            return redirect()->route('escola.usuarios.index')
                ->with('error', 'Usuário não pertence nem está vinculado a esta escola.');
        }

        // 🧱 2️⃣ Bloqueio entre gestores da mesma escola
        $authTemRoleEscola = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $alvoTemRoleEscola = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        if ($authTemRoleEscola && $alvoTemRoleEscola && $auth->id !== $usuario->id) {
            return redirect()->route('escola.usuarios.index')
                ->with('error', 'Você não pode alterar as roles de outro gestor desta escola.');
        }

        // 🧱 3️⃣ Carrega apenas roles permitidas no contexto da escola
        $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();

        // 🧱 4️⃣ Identifica quais roles estão ativas nesta escola
        $rolesSelecionadas = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->pluck(prefix().'role.id')
            ->toArray();

        return view('escola.usuarios.roles_edit', compact(
            'usuario', 'roles', 'escolaAtual', 'rolesSelecionadas'
        ));

    }

   public function updateRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $schoolId = session('current_school_id');
        $escolaAtual = Escola::find($schoolId);

        if (!$escolaAtual) {
            return redirect()->route('escola.dashboard')->with('error', 'Nenhuma escola selecionada.');
        }

        $request->validate([
            'roles' => 'nullable|array'
        ]);

        $rolesSelecionadas = $request->roles ?? [];

        // 🔹 Protege master e secretaria (não editáveis por ninguém)
        $rolesSuperiores = $usuario->roles()
            ->whereIn('role_name', ['master', 'secretaria'])
            ->exists();

        if ($rolesSuperiores && $auth->id !== $usuario->id) {
            return back()->with('error', 'Usuário com role superior não pode ter roles alteradas pela escola.');
        }

        // 🔹 Protege gestores de outros gestores
        $authTemRoleEscola = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $alvoTemRoleEscola = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        if ($authTemRoleEscola && $alvoTemRoleEscola && $auth->id !== $usuario->id) {
            return back()->with('error', 'Você não pode alterar as roles de outro gestor escolar.');
        }

        // 🔹 Obtém roles atuais do usuário nesta escola
        $p = prefix();
        $rolesAtuais = DB::table($p.'usuario_role')
            ->join($p.'role', $p.'usuario_role.role_id', '=', $p.'role.id')
            ->where($p.'usuario_role.usuario_id', $usuario->id)
            ->where($p.'usuario_role.school_id', $schoolId)
            ->pluck($p.'role.role_name', $p.'role.id')
            ->toArray();

        // 🔹 Identifica roles protegidas (devem ser mantidas sempre)
        $rolesProtegidas = ['master', 'secretaria', 'escola'];

        // 🔹 Mapeia IDs de roles protegidas
        $rolesProtegidasIds = DB::table($p.'role')
            ->whereIn('role_name', $rolesProtegidas)
            ->pluck('id')
            ->toArray();

        // 🔹 Filtra roles permitidas para alteração
        $rolesPermitidasIds = DB::table($p.'role')
            ->whereNotIn('role_name', $rolesProtegidas)
            ->pluck('id')
            ->toArray();

        // 🔹 Calcula roles que permanecerão após a atualização
        $rolesFinais = [];

        // Mantém sempre as protegidas que o usuário já tem
        foreach ($rolesAtuais as $id => $nome) {
            if (in_array($nome, $rolesProtegidas)) {
                $rolesFinais[] = $id;
            }
        }

        // Adiciona as novas roles selecionadas (somente permitidas)
        foreach ($rolesSelecionadas as $id) {
            if (in_array($id, $rolesPermitidasIds)) {
                $rolesFinais[] = $id;
            }
        }

        // Remove duplicatas
        $rolesFinais = array_unique($rolesFinais);

        // 🔹 Apaga todas as roles da escola atual
        DB::table($p.'usuario_role')
            ->where('usuario_id', $usuario->id)
            ->where('school_id', $schoolId)
            ->delete();

        // 🔹 Reinsere as roles finais
        foreach ($rolesFinais as $roleId) {
            DB::table($p.'usuario_role')->insert([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleId,
                'school_id'  => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('escola.usuarios.index')
            ->with('success', 'Roles do usuário atualizadas com sucesso.');
    }


    /*
    🧾 Resumo de todas as proteções aplicadas
        Regra /  Proteção aplicada
        ❌ Não excluir master/super master /  ✅
        ❌ Não excluir secretaria  /  ✅
        ❌ Não excluir a si mesmo  /  ✅
        ❌ Não excluir outro “escola” se for “escola”  /  ✅
        ❌ Não excluir com dependências (professor, aluno, turma, ocorrência)  /  ✅
        🔗 Se for apenas vinculado → remover vínculo, não excluir  /  ✅
        👨‍🏫 Se for professor → remover da tabela syrios_professor /  ✅
        ✅ Excluir totalmente só se for dono (school_id igual à atual) /  ✅
        💥 Tratar exceções e mensagens amigáveis / ✅
        */
    public function destroy(Usuario $usuario)
    {
        $schoolId = session('current_school_id');
        $auth = auth()->user();

        // 🔒 1️⃣ Proteções básicas
        if ($usuario->is_super_master || $usuario->roles->pluck('role_name')->contains('master')) {
            return back()->with('error', '🚫 Não é permitido excluir usuários master ou super master.');
        }

        if ($usuario->roles->pluck('role_name')->contains('secretaria')) {
            return back()->with('error', '🚫 Usuários com papel de secretaria não podem ser excluídos por escolas.');
        }

        // 🚫 2️⃣ O usuário não pode se excluir
        if ($usuario->id === $auth->id) {
            return back()->with('error', '🚫 Você não pode excluir a si mesmo.');
        }

        // 🚫 3️⃣ Usuário com role "escola" não pode excluir outro "escola"
        $authTemRoleEscola = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        $alvoTemRoleEscola = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        if ($authTemRoleEscola && $alvoTemRoleEscola) {
            return back()->with('error', '🚫 Usuário com papel de gestão escolar não pode excluir outro gestor da mesma escola.');
        }

        // 🔍 4️⃣ Verifica se pertence a esta escola
        $isNativo = $usuario->school_id == $schoolId;

        try {
            if ($isNativo) {
                // 💣 Excluir totalmente o usuário apenas se não houver dependências
                $possuiDependencias = DB::table(prefix('professor'))->where('usuario_id', $usuario->id)->exists()
                    || DB::table(prefix('aluno'))->where('usuario_id', $usuario->id)->exists()
                    || DB::table(prefix('ocorrencia'))->where('usuario_id', $usuario->id)->exists()
                    || DB::table(prefix('diretor_turma'))->where('usuario_id', $usuario->id)->exists();

                if ($possuiDependencias) {
                    return back()->with('error', '⚠️ Não é possível excluir este usuário, pois ele possui registros vinculados.');
                }

                // Remove vínculos da tabela pivot
                $usuario->roles()->detach();

                // Remove também vínculo em syrios_professor (se existir)
                DB::table(prefix('professor'))
                    ->where('usuario_id', $usuario->id)
                    ->where('school_id', $schoolId)
                    ->delete();

                // Agora exclui o usuário
                $usuario->delete();

                return back()->with('success', '✅ Usuário excluído com sucesso.');
            }

            // 🧩 5️⃣ Se for apenas vinculado (pivot)
            $pivotRoles = $usuario->roles()
                ->wherePivot('school_id', $schoolId)
                ->pluck('role_id')
                ->toArray();

            if (empty($pivotRoles)) {
                return back()->with('warning', '⚠️ Este usuário não possui vínculo com a escola atual.');
            }

            // Verifica se pode remover (sem violar dependências)
            $possuiProfessor = DB::table(prefix('professor'))
                ->where('usuario_id', $usuario->id)
                ->where('school_id', $schoolId)
                ->exists();

            if ($possuiProfessor) {
                DB::table(prefix('professor'))
                    ->where('usuario_id', $usuario->id)
                    ->where('school_id', $schoolId)
                    ->delete();
            }

            // Remove vínculos apenas desta escola
            DB::table(prefix('usuario_role'))
                ->where('usuario_id', $usuario->id)
                ->where('school_id', $schoolId)
                ->delete();

            return back()->with('success', '✅ Vínculo do usuário com a escola removido com sucesso.');

        } catch (\Throwable $e) {
            return back()->with('error', '❌ Erro ao excluir usuário: ' . $e->getMessage());
        }
    }

    private function authorizeEscola($usuario)
    {
        if ($usuario->school_id !== auth()->user()->school_id) {
            abort(403, 'Acesso negado.');
        }
    }

    /**
     * Exibe o formulário de edição respeitando as regras de contexto.
     */
    public function edit(string $id)
    {
        // 1) Identifica contexto (escola atual) e atores
        $schoolId = (int) session('current_school_id'); // deve estar setado no middleware/contexto
        $auth = auth()->user();

        // 2) Carrega o usuário alvo; 404 se não existe
        /** @var Usuario $alvo */
        $alvo = Usuario::query()->findOrFail($id);

        // 3) Calcula matriz de permissões/estado conforme regras do projeto
        $matrix = $this->computeEditMatrix($auth->id, $alvo->id, $schoolId);

        // 4) Usuário externo? (sem qualquer vínculo com a escola atual) → bloqueado
        if (!$matrix['tem_vinculo_com_escola']) {
            return redirect()
                ->route('escola.usuarios.index')
                ->with('error', 'Acesso bloqueado: usuário sem vínculo com a escola atual.');
        }

        // 5) Se protegido (master/secretaria) ou gestor protegido, apenas view-only
        //    Não redirecionamos; mostramos a tela com os campos desabilitados e motivo.
        $motivosBloqueio = $this->motivosBloqueio($matrix);

        // 6) Define o payload para o Blade (sem duplicar lógica lá)
        $payload = [
            'usuario' => $alvo,
            'flags' => [
                'can_edit_password' => $matrix['can_edit_password'],
                'can_edit_nome'     => $matrix['can_edit_nome'],
                'can_edit_status'   => $matrix['can_edit_status'],
                'view_only'         => !$matrix['can_edit_password'] && !$matrix['can_edit_nome'] && !$matrix['can_edit_status'],
            ],
            'contexto' => [
                'is_self'       => $matrix['is_self'],
                'is_nativo'     => $matrix['is_nativo_na_escola'],
                'is_vinculado'  => $matrix['is_vinculado_na_escola'],
                'is_protegido'  => $matrix['is_master_ou_secretaria'] || $matrix['protecao_entre_gestores'],
                'motivos'       => $motivosBloqueio,
            ],
        ];

        // 7) Renderiza o formulário único de edição (o Blade usará os flags acima)
        return view('escola.usuarios.edit', $payload);
    }

    public function update(Request $request, string $id)
    {
        $schoolId = (int) session('current_school_id');
        $auth = auth()->user();

        /** @var Usuario $alvo */
        $alvo = Usuario::query()->findOrFail($id);

        // Matriz de regras/permissões
        $matrix = $this->computeEditMatrix($auth->id, $alvo->id, $schoolId);

        // Usuário externo? bloqueia
        if (!$matrix['tem_vinculo_com_escola']) {
            return back()->with('error', 'Ação negada: usuário sem vínculo com a escola atual.');
        }

        // Proteções gerais (permite self trocar a própria senha)
        if (($matrix['is_master_ou_secretaria'] || $matrix['protecao_entre_gestores']) && !$matrix['is_self']) {
            return back()->with('error', 'Usuário protegido — não pode ser alterado.');
        }

        // Validações condicionais conforme permissões
        $rules = [];
        if ($matrix['can_edit_nome']) {
            // o input do form continua sendo 'nome', mas a coluna é 'nome_u'
            $rules['nome'] = ['sometimes', 'string', 'min:2', 'max:100'];
        }
        if ($matrix['can_edit_status']) {
            $rules['status'] = ['sometimes']; // normalizamos abaixo
        }
        if ($matrix['can_edit_password']) {
            // permite vazio; se vier preenchida, valida min/confirmed
            $rules['password'] = ['sometimes', 'nullable', 'string', 'confirmed', 'min:6'];
        }

        if (empty($rules)) {
            return back()->with('error', 'Não há campos que você possa editar neste contexto.');
        }

        $request->validate($rules);

        // Aplica atualizações permitidas
        $mudouAlgo = false;

        if ($matrix['can_edit_nome'] && $request->filled('nome')) {
            $alvo->nome_u = $request->input('nome');     // ✅ coluna correta
            $mudouAlgo = true;
        }

        if ($matrix['can_edit_status'] && $request->has('status')) {
            $status = $request->input('status');
            if (is_string($status)) {
                $status = in_array(strtolower($status), ['1','ativo','active','on','true'], true) ? 1 : 0;
            }
            $alvo->status = (int) !!$status;             // ✅ tinyint(1)
            $mudouAlgo = true;
        }

        if ($matrix['can_edit_password'] && $request->filled('password')) {
            $alvo->senha_hash = Hash::make($request->input('password')); // ✅ coluna correta
            $mudouAlgo = true;
        }

        if ($mudouAlgo) {
            $alvo->save();
            return back()->with('success', 'Dados atualizados com sucesso.');
        }

        return back()->with('info', 'Nada para atualizar.');
    }

    protected function computeEditMatrix(int $authId, int $alvoId, int $schoolId): array
    {
        $p = prefix(); // ex: syrios_

        // 1️⃣ Relações via pivot
        $pivot = DB::table($p.'usuario_role');

        // 2️⃣ Flags base
        $isSelf = ($authId === $alvoId);

        // 3️⃣ Vínculo com a escola atual
        $temVinculo = $pivot
            ->where('usuario_id', $alvoId)
            ->where('school_id', $schoolId)
            ->exists();

        // 4️⃣ Master/Secretaria
        $roleIdsMasterSecretaria = DB::table($p.'role')
            ->whereIn('role_name', ['master', 'secretaria'])
            ->pluck('id');

        $isMasterOuSecretaria = DB::table($p.'usuario_role')
            ->where('usuario_id', $alvoId)
            ->whereIn('role_id', $roleIdsMasterSecretaria)
            ->exists();

        // 5️⃣ Gestores (role escola) na escola atual
        $roleIdGestor = DB::table($p.'role')->where('role_name', 'escola')->value('id');

        $alvoEhGestorEscola = $roleIdGestor
            ? DB::table($p.'usuario_role')
                ->where('usuario_id', $alvoId)
                ->where('role_id', $roleIdGestor)
                ->where('school_id', $schoolId)
                ->exists()
            : false;

        $authEhGestorEscola = $roleIdGestor
            ? DB::table($p.'usuario_role')
                ->where('usuario_id', $authId)
                ->where('role_id', $roleIdGestor)
                ->where('school_id', $schoolId)
                ->exists()
            : false;

        // 6️⃣ Nativo / Vinculado
        $alvoRow = DB::table($p.'usuario')->where('id', $alvoId)->first();
        $isNativo = $alvoRow && ((int)$alvoRow->school_id === $schoolId);
        $isVinculado = $temVinculo && !$isNativo;

        // 7️⃣ Permissões básicas
        $canEditPassword = $isSelf || $isNativo;
        $canEditNome     = $isNativo && !$isSelf;
        $canEditStatus   = $isNativo && !$isSelf;

        // 8️⃣ Proteção entre gestores da mesma escola (💥 regra mais importante)
        $protecaoEntreGestores = ($authEhGestorEscola && $alvoEhGestorEscola && !$isSelf);

        if ($protecaoEntreGestores) {
            $canEditNome     = false;
            $canEditStatus   = false;
            $canEditPassword = false;
        }

        // 9️⃣ Master/Secretaria — só podem editar própria senha
        if ($isMasterOuSecretaria) {
            $canEditNome   = false;
            $canEditStatus = false;
            if (!$isSelf) {
                $canEditPassword = false;
            }
        }

        // 🔟 Retorno consolidado (sem alterar nada no controller)
        return [
            'is_self'                 => $isSelf,
            'tem_vinculo_com_escola'  => $temVinculo,
            'is_nativo_na_escola'     => $isNativo,
            'is_vinculado_na_escola'  => $isVinculado,
            'is_master_ou_secretaria' => $isMasterOuSecretaria,
            'alvo_eh_gestor_da_escola'=> $alvoEhGestorEscola,
            'auth_eh_gestor_da_escola'=> $authEhGestorEscola,
            'protecao_entre_gestores' => $protecaoEntreGestores,
            'can_edit_password'       => $canEditPassword,
            'can_edit_nome'           => $canEditNome,
            'can_edit_status'         => $canEditStatus,
        ];
    }



    /**
     * Lista os motivos de bloqueio (para exibir no Blade em alertas informativos).
     */
    protected function motivosBloqueio(array $m): array
    {
        $motivos = [];
        if (!$m['tem_vinculo_com_escola']) {
            $motivos[] = 'Sem vínculo com a escola atual';
        }
        if ($m['is_master_ou_secretaria']) {
            $motivos[] = 'Usuário com role master/secretaria é protegido';
        }
        if ($m['protecao_entre_gestores']) {
            $motivos[] = 'Gestor não pode editar outro gestor da mesma escola';
        }
        if (!$m['can_edit_nome'] && !$m['can_edit_status'] && !$m['can_edit_password']) {
            $motivos[] = 'Nenhum campo é editável neste contexto';
        }
        return $motivos;
    }

}
