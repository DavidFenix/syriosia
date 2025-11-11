<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{Usuario, Escola, Role};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class UsuarioController extends Controller
{
    
    /*
        |--------------------------------------------------------------------------
        | 📋 INDEX — Lista de Usuários da Secretaria
        |--------------------------------------------------------------------------
        | Regras e comportamento:
        | • Exibe todos os usuários que pertencem à secretaria logada e suas escolas filhas.
        | • Inclui tanto:
        |     - Usuários com school_id pertencente à secretaria ou filhas;
        |     - Quanto os vinculados via pivot (usuario_role.school_id) a essas escolas.
        | • Evita duplicação de usuários ao agrupar por ID.
        | • Cada vínculo (role + escola) pode gerar uma linha diferente no Blade.
        |
        | Destaques visuais:
        | • Mostra a role e a escola correspondente.
        | • Mostra 🔗 “Vinculado” quando o usuário foi associado via pivot.
        | • Mostra 🏛️ e destaque quando o usuário é a própria secretaria ativa.
        |
        | Proteções:
        | • Usuário logado (role secretaria) e colegas secretários da mesma unidade
        |   aparecem com cadeado 🔒, sem permissão de exclusão.
        */
    public function index()
    {
        $currentSchoolId = session('current_school_id');

        if (!$currentSchoolId) {
            return redirect()->route('home')->with('error', 'Nenhuma escola selecionada no momento.');
        }

        $secretaria = Escola::find($currentSchoolId);

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Escola atual não encontrada.');
        }

        // 🧩 1. Identifica todas as escolas da secretaria (ela mesma + filhas)
        $idsEscolas = collect([$secretaria->id])
            ->merge($secretaria->filhas()->pluck('id'))
            ->unique();

        // 🧩 2. Busca usuários:
        // - cujo school_id pertence à secretaria ou filhas (usuário "nativo" da escola)
        // - OU que estejam vinculados via pivot (usuario_role.school_id)
        $usuarios = Usuario::whereIn('school_id', $idsEscolas)
            ->orWhereHas('roles', function ($q) use ($idsEscolas) {
                $q->whereIn(prefix('usuario_role') . '.school_id', $idsEscolas);
            })
            ->with(['escola', 'roles'])
            ->get()
            //->unique('id') // evita duplicatas se o usuário aparecer nas duas condições
            ->values();

        return view('secretaria.usuarios.index', compact('usuarios', 'secretaria'));
    }


    /*public function index()
    {
        // Obtém o ID da escola atual da sessão
        $currentSchoolId = session('current_school_id');

        // Verifica se há uma escola selecionada
        if (!$currentSchoolId) {
            return redirect()->route('home')->with('error', 'Nenhuma escola selecionada no momento.');
        }

        // Busca a escola (secretaria) correspondente
        $secretaria = Escola::find($currentSchoolId);

        // Garante que seja válida
        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Escola atual não encontrada.');
        }

        // Pega todos os usuários das escolas filhas da secretaria atual
        $usuarios = Usuario::whereIn('school_id', $secretaria->filhas()->pluck('id'))
            ->with(['escola', 'roles'])
            ->get();

        return view('secretaria.usuarios.index', compact('usuarios', 'secretaria'));
    }*/

    /*
        |--------------------------------------------------------------------------
        | ➕ CREATE — Formulário de Novo Usuário
        |--------------------------------------------------------------------------
        | Regras e comportamento:
        | • Exibe formulário para criação de novos usuários dentro da hierarquia da secretaria.
        | • A lista de escolas no select inclui:
        |     - A própria secretaria logada;
        |     - Suas escolas filhas (subordinadas).
        |
        | Restrições:
        | • Não permite criar usuários fora da secretaria ou suas escolas filhas.
        | • Roles disponíveis: todas exceto “master” e “secretaria”.
        | • Usuário logado na secretaria NÃO deve criar usuários dentro da própria secretaria;
        |   deve criar apenas para escolas filhas.
        |
        | UX:
        | • Se o CPF já existir, não cria novamente — mostra aviso para vincular.
        | • Caso o usuário já esteja vinculado à escola/role selecionada, exibe aviso
        |   “já está vinculado” e não mostra botão de vínculo.
        */
    public function create()
    {
        $auth = auth()->user();

        // 🔒 Lista apenas as escolas da própria secretaria + filhas
        $escolas = Escola::where(function ($q) use ($auth) {
            $q->where('id', $auth->school_id)
              ->orWhere('secretaria_id', $auth->school_id);
        })->get();

        $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();

        return view('secretaria.usuarios.create', compact('escolas', 'roles'));

    }

    /*
        |--------------------------------------------------------------------------
        | 💾 STORE — Criação e Vínculo de Usuário
        |--------------------------------------------------------------------------
        | Regras de negócio aplicadas:
        | 1️⃣ Validações iniciais:
        |     • nome_u, cpf, senha e school_id obrigatórios.
        |     • Escola deve pertencer à secretaria logada (ou ser filha dela).
        |
        | 2️⃣ Lógica de CPF existente:
        |     • Se CPF pertence ao Super Master → bloqueia.
        |     • Se CPF pertence a um Master → bloqueia (somente Super Master pode).
        |     • Se CPF já existe e é válido → mostra botão “Vincular”.
        |
        | 3️⃣ Criação de novo usuário:
        |     • Cria usuário com school_id da escola selecionada.
        |     • Define status ativo e senha hash.
        |     • Vincula as roles selecionadas via tabela pivot (usuario_role).
        |
        | 4️⃣ Proteções e coerência:
        |     • Garante que a secretaria logada só cria usuários para escolas filhas.
        |     • Impede criar usuários diretamente na própria secretaria (exceto pelo Master).
        |     • Multi-role permitido (ex: secretaria também pode ter role escola).
        |
        | 5️⃣ Mensagens de retorno:
        |     • CPF já existente → aviso para vincular.
        |     • Sucesso → “Usuário criado com sucesso!”.
        |     • Bloqueio → mensagens específicas conforme tipo de usuário (master, super master, etc.).
        */
    public function store(Request $request)
    {
        $auth = auth()->user();

        $request->validate([
            'nome_u'    => 'required|string|max:100',
            'cpf'       => 'required|string|max:20',
            'school_id' => 'required|integer',
        ]);

        // 🔒 Garante que a escola pertence à secretaria **e não é a própria secretaria**
        $escolaAutorizada = Escola::where('id', $request->school_id)
            ->where('secretaria_id', $auth->school_id)
            ->exists();

        if (!$escolaAutorizada) {
            return back()
                ->withInput()
                ->with('error', '🚫 Você só pode criar usuários em escolas filhas da sua secretaria (não na própria secretaria).');
        }


        // // 🔒 Garante que a escola pertence à secretaria
        // $escolaAutorizada = Escola::where('id', $request->school_id)
        //     ->where(function ($q) use ($auth) {
        //         $q->where('id', $auth->school_id)
        //           ->orWhere('secretaria_id', $auth->school_id);
        //     })
        //     ->exists();

        // if (!$escolaAutorizada) {
        //     return back()
        //         ->withInput()
        //         ->with('error', 'Você só pode criar usuários em escolas da sua secretaria ou filhas.');
        // }

        // 🔎 Verifica CPF existente
        $usuarioExistente = Usuario::where('cpf', $request->cpf)->first();

        if ($usuarioExistente) {
            // 🚫 Super Master
            if ($usuarioExistente->is_super_master) {
                return back()->with('error', 'Este CPF pertence ao Super Master e não pode ser vinculado.')->withInput();
            }

            // 🚫 Master
            if ($usuarioExistente->roles->pluck('role_name')->contains('master')) {
                return back()->with('error', 'Este CPF pertence a um Master. Somente o Super Master pode vinculá-lo.')->withInput();
            }

            // ✅ CPF existente, mas permitido
            return back()
                ->withInput()
                ->with('usuario_existente', $usuarioExistente->id);
        }

        // ✅ Criação de novo usuário
        $request->validate([
            'senha' => 'required|string|min:6',
        ]);

        $usuario = Usuario::create([
            'nome_u'     => $request->nome_u,
            'cpf'        => $request->cpf,
            'senha_hash' => Hash::make($request->senha),
            'status'     => 1,
            'school_id'  => $request->school_id,
        ]);

        // 🔗 Vincula roles (sempre dentro da hierarquia da secretaria)
        if ($request->filled('roles')) {
            foreach ($request->roles as $roleId) {
                $usuario->roles()->attach($roleId, ['school_id' => $request->school_id]);
            }
        }

        return redirect()
            ->route('secretaria.usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /*
        🧠 Resumo lógico
        Situação                                                        Permitido?   Justificativa
        Secretaria logada tenta criar usuário para ela mesma            ❌ Não       Administração da própria secretaria é papel do Master
        Secretaria logada cria/vincula usuário para uma escola filha    ✅ Sim   Faz parte da função administrativa da secretaria
        Mesmo usuário é secretaria e professor em escolas filhas        ✅ Sim   Multi-role contextual, comportamento esperado
        Super Master cria ou altera qualquer vínculo                    ✅ Sempre    Super Master tem poder total
        */
    /*
        |--------------------------------------------------------------------------
        | 🔗 VINCULAR — Associação de Usuário Existente a uma Escola/Role
        |--------------------------------------------------------------------------
        | Objetivo:
        | • Permitir que uma Secretaria vincule um usuário existente a uma nova escola filha,
        |   atribuindo novas roles (ex: professor, escola, coordenador etc).
        |
        | Lógica principal:
        | 1️⃣ Validação:
        |     • school_id deve existir e pertencer à hierarquia da secretaria.
        |     • roles[] é obrigatório e deve conter IDs válidos.
        |
        | 2️⃣ Proteções:
        |     • Impede duplicar vínculos (mesmo usuário + mesma escola + mesma role).
        |     • Impede vincular o próprio usuário da secretaria à escola/secretaria ativa.
        |     • Impede que um usuário com role secretaria seja vinculado novamente como secretaria
        |       na mesma unidade ativa.
        |     • Impede vincular o Super Master ou Master (exceto pelo Super Master autenticado).
        |
        | 3️⃣ Multi-role permitido:
        |     • Usuário pode ser, por exemplo, “secretaria” em uma escola e “professor” em outra.
        |     • Vinculação de secretaria → escola é aceita (desde que não seja na secretaria ativa).
        |
        | 4️⃣ Inserção:
        |     • Se tudo válido, cria os registros na pivot `usuario_role`
        |       com `school_id`, `role_id`, `created_at`, `updated_at`.
        |
        | 5️⃣ Retornos:
        |     • Caso duplicado → aviso “já está vinculado”.
        |     • Caso inválido → mensagens de bloqueio específicas.
        |     • Caso sucesso → “Usuário vinculado com sucesso!”.
        |
        | 6️⃣ Segurança geral:
        |     • Todas as verificações consideram a escola ativa em sessão (`current_school_id`)
        |       e o usuário autenticado (`auth()->user()`).
        */
    public function vincular(Request $request, $usuarioId)
    {
        $usuario = Usuario::findOrFail($usuarioId);
        $auth = auth()->user();
        $currentSchoolId = session('current_school_id');

        $request->validate([
            'school_id' => 'required|integer|exists:' . prefix('escola') . ',id',
            'roles'     => 'array|required|min:1'
        ]);

        // 🚫 Secretaria não pode vincular usuários na própria secretaria
        $currentSchoolId = session('current_school_id');
        if ($request->school_id == $currentSchoolId) {
            return back()->with('error', '🚫 Não é permitido adicionar usuários à própria secretaria.');
        }


        $novaEscola = Escola::find($request->school_id);

        // 🧱 1️⃣ Impede duplicação exata (mesmo user, escola, role)
        $duplicadas = [];
        foreach ($request->roles as $roleId) {
            $jaExiste = DB::table(prefix('usuario_role'))
                ->where('usuario_id', $usuario->id)
                ->where('role_id', $roleId)
                ->where('school_id', $novaEscola->id)
                ->exists();

            if ($jaExiste) {
                $duplicadas[] = $roleId;
            }
        }

        if (!empty($duplicadas)) {
            $nomes = Role::whereIn('id', $duplicadas)->pluck('role_name')->implode(', ');
            return back()->with('warning', "⚠️ O usuário já possui as roles: {$nomes} nessa escola.");
        }

        // 🧱 2️⃣ Impede o usuário de se vincular à mesma secretaria onde está logado
        if ($novaEscola->id == $currentSchoolId) {
            return back()->with('warning', '⚠️ O usuário já pertence à secretaria atual.');
        }

        // 🧱 3️⃣ Impede criar/vincular usuários diretamente na própria secretaria logada
        $currentSchoolId = session('current_school_id');
        $novaEscola = Escola::find($request->school_id);

        // Secretaria logada só pode atuar sobre escolas filhas, nunca sobre ela mesma
        if ($novaEscola && $novaEscola->id == $currentSchoolId) {
            return back()->with('error', '🚫 Você não pode criar ou vincular usuários diretamente nesta Secretaria. Use o painel Master para isso.');
        }

        // 🧩 Permite que um usuário (até mesmo com role secretaria) tenha outras roles em escolas filhas
        // Exemplo válido: usuario com role secretaria → também tem role professor em uma escola filha


        // // 🧱 3️⃣ Impede que uma Secretaria seja vinculada como Escola
        // $rolesSelecionadas = Role::whereIn('id', $request->roles)->pluck('role_name')->toArray();
        // $rolesAtuaisUsuario = $usuario->roles->pluck('role_name')->toArray();

        // if (in_array('secretaria', $rolesAtuaisUsuario) && in_array('escola', $rolesSelecionadas)) {
        //     return back()->with('error', '🚫 Uma Secretaria não pode ser vinculada como Escola.');
        // }

        // 🧱 4️⃣ Protege Super Master e Master
        if ($usuario->is_super_master && !$auth->is_super_master) {
            return back()->with('error', '🚫 Não é permitido vincular o Super Master a outras escolas.');
        }

        if ($usuario->roles->pluck('role_name')->contains('master') && !$auth->is_super_master) {
            if ($auth->cpf !== $usuario->cpf) {
                return back()->with('error', '🚫 Apenas o próprio Master ou o Super Master podem vincular um Master.');
            }
        }

        // ✅ 5️⃣ Tudo certo — cria vínculos
        foreach ($request->roles as $roleId) {
            DB::table(prefix('usuario_role'))->insert([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleId,
                'school_id'  => $novaEscola->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ✅ 6️⃣ Atualiza data de atualização do usuário
        $usuario->touch();

        return redirect()
            ->route('secretaria.usuarios.index')
            ->with('success', "✅ Usuário '{$usuario->nome_u}' vinculado à escola '{$novaEscola->nome_e}' com sucesso!");
    }

    public function edit(Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $currentSchoolId = session('current_school_id');

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada.');
        }

        // 🧱 1️⃣ Verifica se pertence à hierarquia da secretaria
        $idsPermitidos = $secretaria->filhas()->pluck('id')->push($secretaria->id);
        if (!$idsPermitidos->contains($usuario->school_id)) {
            return redirect()->route('secretaria.usuarios.index')
                ->with('error', 'Usuário não pertence à sua secretaria.');
        }

        // 🧱 2️⃣ Monta agrupamento de roles por escola
        $rolesPorEscola = $usuario->roles()
            ->select('role_name', prefix('usuario_role') . '.school_id')
            ->get()
            ->groupBy('school_id');

        // 🧱 3️⃣ Cenário 1 — o próprio usuário logado
        if ($usuario->id === $auth->id) {
            return view('secretaria.usuarios.self_edit', compact('usuario', 'rolesPorEscola'));
        }

        // 🧱 4️⃣ Cenário 2 — outro secretário
        $isSecretarioAqui = $usuario->roles()
            ->where('role_name', 'secretaria')
            ->wherePivot('school_id', $currentSchoolId)
            ->exists();

        if ($isSecretarioAqui) {
            return view('secretaria.usuarios.view_only', compact('usuario', 'rolesPorEscola'))
                ->with('warning', 'Visualização apenas — não é possível editar outro secretário.');
        }

        // 🧱 5️⃣ Cenário 3 — usuário comum (escolas filhas)
        $escolas = collect([$secretaria])->merge($secretaria->filhas()->get());
        $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();

        return view('secretaria.usuarios.edit', compact('usuario', 'escolas', 'roles', 'rolesPorEscola', 'secretaria'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada.');
        }

        $idsPermitidos = $secretaria->filhas()->pluck('id')->push($secretaria->id);

        if (!$idsPermitidos->contains($usuario->school_id)) {
            return redirect()->route('secretaria.usuarios.index')
                ->with('error', 'Usuário fora da sua hierarquia.');
        }

        $isSelf = $auth->id === $usuario->id;
        $usuarioEhSecretario = $usuario->roles->pluck('role_name')->contains('secretaria');

        // 🔒 Bloqueia edição de outro secretário
        if (!$isSelf && $usuarioEhSecretario) {
            return back()->with('error', 'Você não pode editar outro usuário com role secretaria.');
        }

        // 🔒 Se for o próprio: só senha
        if ($isSelf) {
            $request->validate(['senha' => 'nullable|string|min:6|confirmed']);

            if ($request->filled('senha')) {
                $usuario->update(['senha_hash' => \Hash::make($request->senha)]);
                return back()->with('success', 'Senha atualizada com sucesso.');
            }
            return back()->with('success', 'Nada para atualizar.');
        }

        // 🔒 Usuário comum — escola NÃO pode ser trocada
        $request->validate([
            'nome_u' => 'required|string|max:100',
            'cpf' => ['required', 'string', 'max:20', Rule::unique(prefix('usuario'), 'cpf')->ignore($usuario->id)],
            'status' => 'required|in:0,1',
            'senha' => 'nullable|string|min:6|confirmed',
        ]);

        $usuario->update([
            'nome_u'    => $request->nome_u,
            'cpf'       => $request->cpf,
            'status'    => (int) $request->status,
            'school_id' => $usuario->school_id, // 🔒 mantém a escola original
        ]);

        if ($request->filled('senha')) {
            $usuario->update(['senha_hash' => \Hash::make($request->senha)]);
        }

        return redirect()->route('secretaria.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /*
    public function update(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $currentSchoolId = session('current_school_id');

        // 1️⃣ Se for o próprio usuário logado → apenas atualizar senha
        if ($usuario->id === $auth->id) {
            $request->validate([
                'senha' => 'required|string|min:6|confirmed',
            ]);

            $usuario->update(['senha_hash' => Hash::make($request->senha)]);

            return redirect()->route('secretaria.usuarios.edit', $usuario)
                ->with('success', '✅ Senha atualizada com sucesso!');
        }

        // 2️⃣ Se for outro secretário → bloqueia atualização
        $isSecretarioAqui = $usuario->roles()
            ->where('role_name', 'secretaria')
            ->wherePivot('school_id', $currentSchoolId)
            ->exists();

        if ($isSecretarioAqui) {
            return back()->with('error', '🚫 Você não pode alterar outro secretário.');
        }

        // 3️⃣ Caso normal (usuário comum)
        $request->validate([
            'nome_u'    => 'required|string|max:100',
            'cpf'       => 'required|string|max:20',
            'status'    => 'required|boolean',
            'school_id' => 'required|integer|exists:' . prefix('escola') . ',id',
            'senha'     => 'nullable|string|min:6',
        ]);

        $usuario->update([
            'nome_u'    => $request->nome_u,
            'cpf'       => $request->cpf,
            'status'    => $request->status,
            'school_id' => $request->school_id,
        ]);

        if ($request->filled('senha')) {
            $usuario->update(['senha_hash' => Hash::make($request->senha)]);
        }

        return redirect()->route('secretaria.usuarios.index')
            ->with('success', '✅ Usuário atualizado com sucesso!');
    }*/



    /*
    public function edit(Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $currentSchoolId = session('current_school_id');

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada.');
        }

        // 1️⃣ Verifica se o usuário pertence à hierarquia (secretaria ou filhas)
        $idsPermitidos = $secretaria->filhas()->pluck('id')->push($secretaria->id);
        if (!$idsPermitidos->contains($usuario->school_id)) {
            return redirect()->route('secretaria.usuarios.index')->with('error', 'Usuário não pertence à sua secretaria.');
        }

        // 2️⃣ Impede editar colegas secretários ou o próprio vínculo de secretaria
        $isSecretarioAqui = $usuario->roles()
            ->where('role_name', 'secretaria')
            ->wherePivot('school_id', $currentSchoolId)
            ->exists();

        if ($isSecretarioAqui) {
            return redirect()->route('secretaria.usuarios.index')
                ->with('error', '🚫 Você não pode editar usuários com role "secretaria" nesta secretaria.');
        }

        if ($usuario->id === $auth->id) {
            return redirect()->route('secretaria.usuarios.index')
                ->with('error', '🚫 Você não pode editar seu próprio cadastro de Secretaria.');
        }

        // 3️⃣ Carrega escolas e roles permitidas
        $escolas = collect([$secretaria])->merge($secretaria->filhas()->get());
        $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();

        return view('secretaria.usuarios.edit', compact('usuario', 'escolas', 'roles', 'secretaria'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $currentSchoolId = session('current_school_id');

        $request->validate([
            'nome_u'    => 'required|string|max:100',
            'cpf'       => 'required|string|max:20',
            'status'    => 'required|boolean',
            'school_id' => 'required|integer|exists:' . prefix('escola') . ',id',
            'roles'     => 'array',
            'roles.*'   => 'exists:' . prefix('role') . ',id',
        ]);

        // 1️⃣ Verifica se o usuário pertence à hierarquia
        $idsPermitidos = $secretaria->filhas()->pluck('id')->push($secretaria->id);
        if (!$idsPermitidos->contains($usuario->school_id)) {
            return back()->with('error', '🚫 Usuário fora da hierarquia da secretaria.');
        }

        // 2️⃣ Impede editar colegas secretários e o próprio vínculo
        $isSecretarioAqui = $usuario->roles()
            ->where('role_name', 'secretaria')
            ->wherePivot('school_id', $currentSchoolId)
            ->exists();

        if ($isSecretarioAqui) {
            return back()->with('error', '🚫 Você não pode alterar usuários com role "secretaria" nesta secretaria.');
        }

        if ($usuario->id === $auth->id) {
            return back()->with('error', '🚫 Você não pode alterar seu próprio vínculo de Secretaria.');
        }

        // 3️⃣ Impede que a role “secretaria” seja adicionada nesta secretaria
        $rolesSelecionadas = Role::whereIn('id', $request->roles ?? [])->pluck('role_name')->toArray();
        if (in_array('secretaria', $rolesSelecionadas) && $request->school_id == $currentSchoolId) {
            return back()->with('error', '🚫 Não é permitido adicionar a role "secretaria" na secretaria ativa.');
        }

        // 4️⃣ Atualiza dados básicos
        $usuario->update([
            'nome_u'    => $request->nome_u,
            'cpf'       => $request->cpf,
            'status'    => $request->status,
            'school_id' => $request->school_id,
        ]);

        // Atualiza senha (se informada)
        if ($request->filled('senha')) {
            $usuario->update(['senha_hash' => Hash::make($request->senha)]);
        }

        // 5️⃣ Atualiza roles (exceto as bloqueadas)
        $rolesSync = [];
        foreach ($request->roles ?? [] as $role_id) {
            $rolesSync[$role_id] = ['school_id' => $request->school_id];
        }

        $usuario->roles()->sync($rolesSync);

        return redirect()->route('secretaria.usuarios.index')
            ->with('success', '✅ Usuário atualizado com sucesso.');
    }*/



    /*
        public function vincular(Request $request, $usuarioId)
        {
            $usuario = Usuario::findOrFail($usuarioId);
            $auth = auth()->user();

            $request->validate([
                'school_id' => 'required|integer|exists:' . prefix('escola') . ',id',
                'roles'     => 'array|required|min:1'
            ]);

            $novaEscola = Escola::find($request->school_id);

            // 🧱 1️⃣ Impede duplicação exata (mesmo user, escola, role)
            foreach ($request->roles as $roleId) {
                $jaExiste = DB::table(prefix('usuario_role'))
                    ->where('usuario_id', $usuario->id)
                    ->where('role_id', $roleId)
                    ->where('school_id', $novaEscola->id)
                    ->exists();

                if ($jaExiste) {
                    return back()->with('warning', "⚠️ O usuário já está vinculado a esta escola com a role selecionada.");
                }
            }

            // 🧱 2️⃣ Impede o usuário de se vincular à mesma secretaria onde está logado
            $currentSchoolId = session('current_school_id');
            if ($novaEscola->id == $currentSchoolId) {
                return back()->with('warning', '⚠️ O usuário já pertence à escola/secretaria atual.');
            }

            // 🧱 3️⃣ Impede que uma secretaria seja vinculada como escola
            $rolesSelecionadas = Role::whereIn('id', $request->roles)->pluck('role_name')->toArray();

            if (in_array('secretaria', $usuario->roles->pluck('role_name')->toArray()) && in_array('escola', $rolesSelecionadas)) {
                return back()->with('error', '🚫 Uma Secretaria não pode ser vinculada como Escola.');
            }

            // 🧱 4️⃣ Protege super master e master
            if ($usuario->is_super_master && !$auth->is_super_master) {
                return back()->with('error', '🚫 Não é permitido vincular o Super Master a outras escolas.');
            }

            if ($usuario->roles->pluck('role_name')->contains('master') && !$auth->is_super_master) {
                if ($auth->cpf !== $usuario->cpf) {
                    return back()->with('error', '🚫 Apenas o próprio Master ou o Super Master podem vincular um Master.');
                }
            }

            // ✅ 5️⃣ Tudo certo — cria vínculos
            foreach ($request->roles as $roleId) {
                DB::table(prefix('usuario_role'))->insert([
                    'usuario_id' => $usuario->id,
                    'role_id'    => $roleId,
                    'school_id'  => $novaEscola->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()
                ->route('secretaria.usuarios.index')
                ->with('success', "✅ Usuário '{$usuario->nome_u}' vinculado à escola {$novaEscola->nome_e} com sucesso!");
        }

        public function vincular(Request $request, $usuarioId)
        {
            $auth = auth()->user();
            $usuario = Usuario::findOrFail($usuarioId);

            $request->validate([
                'school_id' => 'required|integer',
                'roles'     => 'array|required',
            ]);

            // 🔒 Valida se escola pertence à secretaria
            $escolaAutorizada = Escola::where('id', $request->school_id)
                ->where(function ($q) use ($auth) {
                    $q->where('id', $auth->school_id)
                      ->orWhere('secretaria_id', $auth->school_id);
                })
                ->exists();

            if (!$escolaAutorizada) {
                return back()->with('error', 'A escola selecionada não pertence à sua secretaria.');
            }

            // 🚫 Proteções adicionais
            if ($usuario->is_super_master || $usuario->roles->pluck('role_name')->contains('master')) {
                return back()->with('error', 'Não é permitido vincular Masters ou Super Masters a outras escolas.');
            }

            foreach ($request->roles as $roleId) {
                $jaTem = $usuario->roles()
                    ->where('role_id', $roleId)
                    ->wherePivot('school_id', $request->school_id)
                    ->exists();

                if (!$jaTem) {
                    $usuario->roles()->attach($roleId, ['school_id' => $request->school_id]);
                }
            }

            return redirect()
                ->route('secretaria.usuarios.index')
                ->with('success', 'Usuário existente vinculado com sucesso!');
        }

        public function index()
        {
            $secretaria = auth()->user()->escola;

            if (!$secretaria) {
                return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada.');
            }

            // pega todos os usuários das escolas filhas da secretaria logada
            $usuarios = Usuario::whereIn('school_id', $secretaria->filhas()->pluck('id'))
                ->with(['escola','roles'])
                ->get();

            return view('secretaria.usuarios.index', compact('usuarios','secretaria'));
        }

        public function create()
        {
            $secretaria = auth()->user()->escola;
            
            if (!$secretaria) {
                return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada.');
            }

            // secretária e suas filhas
            $filhas = $secretaria->filhas()->get();
            $escolas = collect([$secretaria])->merge($filhas);

            //$escolas = $secretaria->filhas;
            //$roles = Role::where('role_name', '!=', 'master')->get();
            
            // filtrar roles: exclui master e secretaria
            $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();

            return view('secretaria.usuarios.create', compact('escolas','roles'));
        }

        public function store(Request $request)
        {
            //dd($request->all()); // <- debug, vai mostrar os dados enviados

            $secretaria = auth()->user()->escola;

            $filhasIds = $secretaria->filhas()->pluck('id')->toArray();
            $permitidos = array_merge([$secretaria->id], $filhasIds);

            if (! in_array($request->school_id, $permitidos)) {
                return back()->with('error', 'Escola inválida para esta secretaria.');
            }

            // 🔒 Validação
            $request->validate([
                'nome_u'   => 'required|string|max:100',
                'cpf'      => 'required|string|max:11',
                'senha'    => 'required|string|min:6',
                'status'   => 'required|boolean',
                'school_id'=> 'required|exists:syrios_escola,id',
                'roles'    => 'array',
                'roles.*'  => 'exists:syrios_role,id',
            ]);

            // 🔒 Garante que a escola escolhida pertence à secretaria logada
            if (!$secretaria->filhas->pluck('id')->contains($request->school_id)) {
                return back()->withErrors('Escola inválida para esta secretaria.');
            }

            // 🔨 Cria o usuário
            $usuario = Usuario::create([
                'nome_u'    => $request->nome_u,
                'cpf'       => $request->cpf,
                'senha_hash'=> Hash::make($request->senha),
                'status'    => $request->status,
                'school_id' => $request->school_id,
            ]);

            // 🔨 Vincula roles (com school_id)
            $rolesSync = [];
            foreach ($request->roles ?? [] as $role_id) {
                $rolesSync[$role_id] = ['school_id' => $request->school_id];
            }
            $usuario->roles()->sync($rolesSync);

            return redirect()->route('secretaria.usuarios.index')
                ->with('success', 'Usuário criado com sucesso.');
        }
    */

    /*public function update(Request $request, Usuario $usuario)
    {
        $secretaria = auth()->user()->escola;

        $filhasIds = $secretaria->filhas()->pluck('id')->toArray();
        $permitidos = array_merge([$secretaria->id], $filhasIds);

        if (! in_array($request->school_id, $permitidos)) {
            return back()->with('error', 'Escola inválida para esta secretaria.');
        }

        // 🔒 Validação
        $request->validate([
            'nome_u'   => 'required|string|max:100',
            'cpf'      => 'required|string|max:11',
            'status'   => 'required|boolean',
            'school_id'=> 'required|exists:syrios_escola,id',
            'senha'    => 'nullable|string|min:6',
            'roles'    => 'array',
            'roles.*'  => 'exists:syrios_role,id',
        ]);

        // 🔒 Garante que a escola escolhida pertence à secretaria logada
        if (!$secretaria->filhas->pluck('id')->contains($request->school_id)) {
            return back()->withErrors('Escola inválida para esta secretaria.');
        }

        // 🔨 Atualiza usuário
        $usuario->update([
            'nome_u'    => $request->nome_u,
            'cpf'       => $request->cpf,
            'status'    => $request->status,
            'school_id' => $request->school_id,
        ]);

        // Atualiza senha (se enviada)
        if ($request->filled('senha')) {
            $usuario->update(['senha_hash' => Hash::make($request->senha)]);
        }

        // 🔨 Atualiza roles (com school_id)
        $rolesSync = [];
        foreach ($request->roles ?? [] as $role_id) {
            $rolesSync[$role_id] = ['school_id' => $request->school_id];
        }
        $usuario->roles()->sync($rolesSync);

        return redirect()->route('secretaria.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }*/

    /*
        public function store(Request $request)
        {
            $request->validate([
                'nome_u' => 'required',
                'cpf' => 'required|unique:syrios_usuario,cpf',
                'senha' => 'required|min:6',
                'school_id' => 'required|exists:syrios_escola,id',
            ]);

            $usuario = Usuario::create([
                'nome_u' => $request->nome_u,
                'cpf' => $request->cpf,
                'senha_hash' => Hash::make($request->senha),
                'status' => 1,
                'school_id' => $request->school_id,
            ]);

            if ($request->has('roles')) {
                // Monta array com school_id junto
                $rolesSync = [];
                foreach ($request->roles ?? [] as $role_id) {
                    $rolesSync[$role_id] = ['school_id' => $request->school_id];
                }

                // Salva as roles vinculadas
                $usuario->roles()->sync($rolesSync);

                //$usuario->roles()->sync($request->roles);
            }

            return redirect()->route('secretaria.usuarios.index')->with('success', 'Usuário criado!');
        }

        public function update(Request $request, Usuario $usuario)
        {
            $secretaria = auth()->user()->escola;

            if (!$secretaria->filhas->contains($usuario->school_id)) {
                return redirect()->route('secretaria.usuarios.index')->with('error','Usuário não permitido.');
            }

            $usuario->update([
                'nome_u' => $request->nome_u,
                'cpf' => $request->cpf,
                'school_id' => $request->school_id,
            ]);

            if ($request->filled('senha')) {
                $usuario->update(['senha_hash' => Hash::make($request->senha)]);
            }

            // No store e no update, antes de sync():
            $rolesValidos = Role::whereNotIn('role_name', ['master', 'secretaria'])
                        ->pluck('id')
                        ->toArray();

            $rolesSelecionadas = $request->roles ?? [];
            $rolesFiltradas = array_intersect($rolesSelecionadas, $rolesValidos);

            //não deixa salvar roles proibidos para secretaria
            $usuario->roles()->sync($rolesFiltradas);

            return redirect()->route('secretaria.usuarios.index')->with('success', 'Usuário atualizado!');
        }
    */

    /*public function edit(Usuario $usuario)
    {
        $secretaria = auth()->user()->escola;

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada.');
        }

        if (!$secretaria->filhas->contains($usuario->school_id)) {
            return redirect()->route('secretaria.usuarios.index')->with('error','Usuário não permitido.');
        }

        // secretária e suas filhas
        $filhas = $secretaria->filhas()->get();
        $escolas = collect([$secretaria])->merge($filhas);

        //$escolas = $secretaria->filhas;
        //$roles = Role::where('role_name', '!=', 'master')->get();
        
        // filtrar roles (sem master e secretaria)
        $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();


        return view('secretaria.usuarios.edit', compact('usuario','escolas','roles','secretaria'));
    }*/

    /*
        💬 Resumo do novo comportamento
        Situação                                                Resultado
        Secretaria tenta excluir a si mesma (role secretaria)   🔒 bloqueado
        Secretaria tenta excluir outro secretário               🔒 bloqueado
        Secretaria exclui vínculo “professor” de um secretário  ✅ permitido
        Secretaria exclui vínculo de usuário comum              ✅ permitido
        Secretaria exclui vínculo com FK ativa                  ⚠️ mensagem “registro dependente”
        Usuário sem vínculos após exclusão                      🗑️ usuário deletado por completo
        */
    /*
        |--------------------------------------------------------------------------
        | 🗑️ DESTROY — Remoção de Vínculo ou Exclusão de Usuário
        |--------------------------------------------------------------------------
        | Objetivo:
        | • Permitir à Secretaria remover vínculos de usuários dentro de sua hierarquia,
        |   respeitando as restrições de papel (role) e relações com outras tabelas.
        |
        | Lógica principal:
        | 1️⃣ Identificação do vínculo:
        |     • Recebe school_id e role_id no request para saber qual vínculo será removido.
        |     • Um mesmo usuário pode aparecer várias vezes (multi-role), cada linha é independente.
        |
        | 2️⃣ Proteções:
        |     • 🚫 Não pode excluir o próprio vínculo de “secretaria” ativo.
        |     • 🚫 Não pode excluir outros secretários da secretaria atual.
        |     • ✅ Pode excluir outros vínculos (ex: “professor”, “escola”, “coordenador”, etc.).
        |
        | 3️⃣ Tentativa de exclusão:
        |     • Remove o vínculo específico (usuario_role) referente à role e escola informadas.
        |     • Caso o banco retorne erro de chave estrangeira (FK), mostra mensagem:
        |       “⚠️ Este vínculo não pode ser removido porque há registros dependentes.”
        |
        | 4️⃣ Exclusão do usuário completo:
        |     • Se o usuário ficar sem vínculos após a remoção, ele é excluído totalmente.
        |     • Caso ainda possua vínculos, apenas o vínculo removido é afetado.
        |
        | 5️⃣ Retornos e mensagens:
        |     • Sucesso (vínculo ou usuário removido) → “✅ Vínculo removido com sucesso.”
        |     • FK violation → “⚠️ O usuário não pode ser excluído porque há registros dependentes.”
        |     • Tentativa bloqueada → “🚫 Você não pode excluir este vínculo.”
        |
        | 6️⃣ Segurança geral:
        |     • Usa validação dupla (visual e backend) — botão 🔒 no Blade + verificação no controller.
        |     • Toda ação depende da escola ativa em sessão (`current_school_id`).
        */
    public function destroy(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $currentSchoolId = session('current_school_id');
        $schoolId = $request->input('school_id');
        $roleId = $request->input('role_id');

        // 1️⃣ Impede excluir a si mesmo como secretaria da escola ativa
        $isSelfSecretaria = (
            $usuario->id === $auth->id &&
            $schoolId == $currentSchoolId &&
            $usuario->roles()->where('role_name', 'secretaria')
                ->wherePivot('school_id', $currentSchoolId)->exists()
        );

        if ($isSelfSecretaria) {
            return back()->with('error', '🚫 Você não pode excluir sua própria role de Secretaria ativa.');
        }

        // 2️⃣ Impede excluir colegas secretários
        $isColegaSecretaria = (
            $usuario->id !== $auth->id &&
            $usuario->roles()->where('role_name', 'secretaria')
                ->wherePivot('school_id', $currentSchoolId)->exists()
        );

        if ($isColegaSecretaria && $roleId && $schoolId == $currentSchoolId) {
            return back()->with('error', '🚫 Você não pode excluir um colega de Secretaria.');
        }

        // 3️⃣ Tenta remover o vínculo específico (role + escola)
        try {
            $usuario->roles()
                ->wherePivot('school_id', $schoolId)
                ->wherePivot('role_id', $roleId)
                ->detach();
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), '23000')) {
                return back()->with('error', '⚠️ Este vínculo não pode ser removido porque há registros dependentes.');
            }
            throw $e;
        }

        // 4️⃣ Se não restaram vínculos, remove o usuário inteiro
        if (!$usuario->roles()->exists()) {
            try {
                $usuario->delete();
                return redirect()->route('secretaria.usuarios.index')->with('success', '🗑️ Usuário e seus vínculos removidos com sucesso.');
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), '23000')) {
                    return back()->with('error', '⚠️ O usuário não pode ser excluído porque há registros dependentes.');
                }
                throw $e;
            }
        }

        return redirect()
            ->route('secretaria.usuarios.index')
            ->with('success', '✅ Vínculo removido com sucesso.');
    }

    /*
        public function destroy(Usuario $usuario)
        {
            $auth = auth()->user();
            $currentSchoolId = session('current_school_id');
            $secretaria = $auth->escola;

            // 🧱 1️⃣ Impede autoexclusão da role secretaria ativa
            $isSelfSecretaria = $usuario->id === $auth->id &&
                $usuario->roles()
                    ->where('role_name', 'secretaria')
                    ->wherePivot('school_id', $currentSchoolId)
                    ->exists();

            if ($isSelfSecretaria) {
                return back()->with('error', '🚫 Você não pode excluir sua própria role de Secretaria ativa.');
            }

            // 🧱 2️⃣ Impede excluir colegas secretários da mesma secretaria
            $isColegaSecretaria = $usuario->roles()
                ->where('role_name', 'secretaria')
                ->wherePivot('school_id', $currentSchoolId)
                ->exists();

            if ($isColegaSecretaria) {
                return back()->with('error', '🚫 Você não pode excluir um colega de Secretaria nesta unidade.');
            }

            // 🧱 3️⃣ Garante que o vínculo pertence a uma escola da secretaria logada
            $filhasIds = $secretaria->filhas()->pluck('id')->toArray();
            $permitidos = array_merge([$secretaria->id], $filhasIds);

            // Se o vínculo for externo → negar
            $vinculosDoUsuario = $usuario->roles()->pluck(prefix('usuario_role') . '.school_id')->toArray();
            if (!array_intersect($permitidos, $vinculosDoUsuario)) {
                return back()->with('error', '🚫 Usuário não permitido para exclusão nesta Secretaria.');
            }

            // 🧱 4️⃣ Exclui apenas o vínculo da escola ativa
            try {
                $usuario->roles()->wherePivot('school_id', $currentSchoolId)->detach();
            } catch (\Illuminate\Database\QueryException $e) {
                // Se houver FK constraint → erro amigável
                if (str_contains($e->getMessage(), '23000')) {
                    return back()->with('error', '⚠️ Este vínculo não pode ser removido porque está em uso (referenciado em outras tabelas).');
                }
                throw $e; // outro erro desconhecido
            }

            // 🧱 5️⃣ Se não tiver mais vínculos, pode excluir completamente o usuário
            $aindaTemVinculos = $usuario->roles()->exists();

            if (! $aindaTemVinculos) {
                try {
                    $usuario->delete();
                    return redirect()->route('secretaria.usuarios.index')->with('success', '🗑️ Usuário e seus vínculos removidos com sucesso.');
                } catch (\Illuminate\Database\QueryException $e) {
                    if (str_contains($e->getMessage(), '23000')) {
                        return back()->with('error', '⚠️ O usuário não pode ser excluído porque há registros dependentes.');
                    }
                    throw $e;
                }
            }

            return redirect()
                ->route('secretaria.usuarios.index')
                ->with('success', '🔗 Vínculo do usuário removido com sucesso.');
        }


        public function destroy(Usuario $usuario)
        {
            $secretaria = auth()->user()->escola;

            if (!$secretaria->filhas->contains($usuario->school_id)) {
                return redirect()->route('secretaria.usuarios.index')->with('error','Usuário não permitido.');
            }

            // Remove os vínculos na tabela pivot primeiro
            $usuario->roles()->detach();

            // Agora pode excluir o usuário
            $usuario->delete();

            return redirect()->route('secretaria.usuarios.index')->with('success', 'Usuário excluído!');
        }
    */

    /**
     * ============================================================
     * 🎛️ GERENCIAMENTO DE ROLES (Secretaria)
     * ============================================================
     * Permite que a secretaria gerencie as roles de usuários
     * vinculados às escolas filhas.
     *
     * 🧠 Regras principais:
     * - Secretaria só gerencia escolas que administra (filhas).
     * - Não pode editar roles dentro da própria secretaria.
     * - Não pode alterar suas próprias roles.
     * - Não pode alterar roles de outros secretários.
     * - Não pode atribuir roles 'master' ou 'secretaria'.
     * ============================================================
     */
    /*public function editRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $currentSchoolId = session('current_school_id');
        $schoolIdSelecionada = $request->query('school_id');

        // 🔒 Garante que o usuário da sessão é realmente uma secretaria
        if (!$auth->hasRole('secretaria')) {
            return redirect()->route('home')->with('error', 'Acesso negado: apenas secretarias podem gerenciar roles.');
        }

        // 🧩 Coleta todas as escolas sob administração da secretaria (ela mesma + filhas)
        $escolas = collect([$secretaria])->merge($secretaria->filhas()->get());

        // 🔒 Garante que o usuário alvo pertence a esta secretaria ou às filhas
        $idsPermitidos = $escolas->pluck('id');
        if (!$idsPermitidos->contains($usuario->school_id)) {
            return redirect()->route('secretaria.usuarios.index')
                ->with('error', 'Usuário fora da hierarquia da secretaria.');
        }

        // 📚 Todas as roles (exceto master e secretaria)
        $roles = Role::whereNotIn('role_name', ['master', 'secretaria'])->get();

        // 🔍 Roles já atribuídas ao usuário na escola selecionada
        $rolesSelecionadas = [];
        if ($schoolIdSelecionada) {
            $rolesSelecionadas = $usuario->roles()
                ->wherePivot('school_id', $schoolIdSelecionada)
                ->pluck('role_id')
                ->toArray();
        }

        return view('secretaria.usuarios.roles', compact(
            'usuario',
            'roles',
            'rolesSelecionadas',
            'escolas',
            'schoolIdSelecionada',
            'secretaria'
        ));
    }*/


    /**
     * ============================================================
     * 💾 Atualiza roles do usuário em uma escola específica
     * ============================================================
     */
    /*public function updateRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $schoolId = $request->input('school_id');

        // 🔒 Validação básica
        $request->validate([
            'school_id' => 'required|integer|exists:' . prefix('escola') . ',id',
            'roles'     => 'array',
            'roles.*'   => 'integer|exists:' . prefix('role') . ',id',
        ]);

        // 🔒 Confirma se a escola pertence à secretaria
        $filhasIds = $secretaria->filhas()->pluck('id')->toArray();
        if (!in_array($schoolId, $filhasIds)) {
            return back()->with('error', 'Você só pode alterar roles em escolas da sua secretaria.');
        }

        // 🔒 Impede que o secretário altere a si mesmo
        if ($auth->id === $usuario->id) {
            return back()->with('error', 'Você não pode alterar suas próprias roles.');
        }

        // 🔒 Impede alterar secretários desta secretaria
        $isSecretarioAqui = $usuario->roles()
            ->where('role_name', 'secretaria')
            ->wherePivot('school_id', $secretaria->id)
            ->exists();

        if ($isSecretarioAqui) {
            return back()->with('error', 'Não é permitido alterar roles de outro secretário desta secretaria.');
        }

        // 🔒 Filtra roles válidas (sem master e secretaria)
        $rolesValidas = Role::whereNotIn('role_name', ['master', 'secretaria'])
            ->pluck('id')
            ->toArray();

        $rolesRequisitadas = array_intersect($request->input('roles', []), $rolesValidas);

        // 🧹 Remove vínculos anteriores na escola atual
        DB::table(prefix('usuario_role'))
            ->where('usuario_id', $usuario->id)
            ->where('school_id', $schoolId)
            ->whereIn('role_id', $rolesValidas)
            ->delete();

        // 🔗 Insere novos vínculos
        foreach ($rolesRequisitadas as $roleId) {
            DB::table(prefix('usuario_role'))->insert([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleId,
                'school_id'  => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('secretaria.usuarios.roles.edit', ['usuario' => $usuario, 'school_id' => $schoolId])
            ->with('success', 'Roles atualizadas com sucesso!');
    }*/

    /**
     * ============================================================
     * 🎛️ GERENCIAMENTO DE ROLES (Secretaria)
     * ============================================================
     * Permite que a secretaria gerencie roles de usuários
     * dentro das escolas filhas.
     *
     * Agora o secretário pode gerenciar suas próprias roles,
     * exceto a role "secretaria", que é fixa e só pode ser alterada pelo Master.
     * ============================================================
     */
    public function editRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $schoolIdSelecionada = $request->query('school_id');

        if (!$auth->hasRole('secretaria')) {
            return redirect()->route('home')->with('error', 'Acesso negado: apenas secretarias podem gerenciar roles.');
        }

        // 🏫 Coleta todas as escolas sob administração da secretaria
        $escolas = collect([$secretaria])->merge($secretaria->filhas()->get());
        $idsPermitidos = $escolas->pluck('id');

        // 🔒 Garante que o usuário alvo pertence à secretaria ou às filhas
        if (!$idsPermitidos->contains($usuario->school_id)) {
            return redirect()->route('secretaria.usuarios.index')
                ->with('error', 'Usuário fora da hierarquia da secretaria.');
        }

        $roles = Role::all();
        $rolesSelecionadas = [];

        if ($schoolIdSelecionada) {
            $rolesSelecionadas = $usuario->roles()
                ->wherePivot('school_id', $schoolIdSelecionada)
                ->pluck('role_id')
                ->toArray();
        }

        // 🔍 Detecta se é o próprio secretário
        $isSelf = $auth->id === $usuario->id;

        return view('secretaria.usuarios.roles', compact(
            'usuario',
            'roles',
            'rolesSelecionadas',
            'escolas',
            'schoolIdSelecionada',
            'secretaria',
            'isSelf'
        ));
    }


    /**
     * ============================================================
     * 💾 Atualiza roles do usuário em uma escola específica
     * ============================================================
     */
    public function updateRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();
        $secretaria = $auth->escola;
        $schoolId = $request->input('school_id');

        $request->validate([
            'school_id' => 'required|integer|exists:' . prefix('escola') . ',id',
            'roles'     => 'array',
            'roles.*'   => 'integer|exists:' . prefix('role') . ',id',
        ]);

        $filhasIds = $secretaria->filhas()->pluck('id')->toArray();
        if (!in_array($schoolId, $filhasIds)) {
            return back()->with('error', 'Você só pode alterar roles em escolas filhas da sua secretaria.');
        }

        $isSelf = $auth->id === $usuario->id;

        // 🔒 Verifica se o usuário é secretário na secretaria ativa
        $isSecretarioAqui = $usuario->roles()
            ->where('role_name', 'secretaria')
            ->wherePivot('school_id', $secretaria->id)
            ->exists();

        // 🚫 Caso 1: secretário alterando outro secretário → proibido
        if (!$isSelf && $isSecretarioAqui) {
            return back()->with('error', 'Você não pode alterar roles de outro secretário desta secretaria.');
        }

        // 🚫 Caso 2: secretário comum alterando usuário fora da hierarquia
        if (!$isSelf && !$filhasIds && !$isSecretarioAqui) {
            return back()->with('error', 'Usuário fora da hierarquia da secretaria.');
        }

        // ✅ Caso 3: secretário alterando a si mesmo
        // Pode alterar suas roles, EXCETO a role "secretaria"
        $rolesValidas = Role::pluck('id', 'role_name')->toArray();
        $roleIdSecretaria = $rolesValidas['secretaria'] ?? null;

        // filtra roles enviadas, sem master e secretaria
        $rolesPermitidas = Role::whereNotIn('role_name', ['master', 'secretaria'])
            ->pluck('id')
            ->toArray();

        $rolesRequisitadas = array_intersect($request->input('roles', []), $rolesPermitidas);

        // 🧹 Remove vínculos anteriores nessa escola (exceto "secretaria")
        $query = DB::table(prefix('usuario_role'))
            ->where('usuario_id', $usuario->id)
            ->where('school_id', $schoolId);

        // Se estiver alterando a si mesmo, preserva o vínculo da role "secretaria"
        if ($isSelf && $roleIdSecretaria) {
            $query->where('role_id', '!=', $roleIdSecretaria);
        }

        $query->delete();

        // 🔗 Insere novos vínculos (mantendo secretaria)
        foreach ($rolesRequisitadas as $roleId) {
            DB::table(prefix('usuario_role'))->insert([
                'usuario_id' => $usuario->id,
                'role_id'    => $roleId,
                'school_id'  => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('secretaria.usuarios.roles.edit', ['usuario' => $usuario, 'school_id' => $schoolId])
            ->with('success', $isSelf
                ? 'Suas roles foram atualizadas com sucesso!'
                : 'Roles do usuário atualizadas com sucesso!'
            );
    }



}

