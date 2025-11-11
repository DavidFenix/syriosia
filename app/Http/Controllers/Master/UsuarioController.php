<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Escola;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $filtro = request('tipo');
        $usuarios = Usuario::with(['escola','roles'])->filtrarPorEscola($filtro)->get();

        //$usuarios = Usuario::with(['escola', 'roles'])->get();
        return view('master.usuarios.index', compact('usuarios','filtro'));
    }

    public function create()
    {
        $escolas = Escola::all();
        $roles   = Role::all();
        return view('master.usuarios.create', compact('escolas', 'roles'));
    }


    /*
    🧠 Novas regras incorporadas
    🚫 Ninguém pode criar (nem vincular) com o CPF de um Super Master, a não ser o próprio Super Master autenticado.
    🚫 Ninguém pode criar com CPF de um Master, a não ser o Super Master ou o próprio Master autenticado.
    ✅ Usuário comum existente: mantém o mesmo comportamento de “mostrar botão de vincular”.
    ✅ Mantém compatibilidade total com o Blade e o vincular() já existentes.
    */
    public function store(Request $request)
    {
        $auth = auth()->user();

        // 🔍 Validação básica inicial
        $request->validate([
            'nome_u'    => 'required|string|max:100',
            'cpf'       => 'required|string|max:20',
            'school_id' => 'required|integer',
        ]);

        // 🔎 Verifica se o CPF já existe
        $usuarioExistente = Usuario::where('cpf', $request->cpf)->first();

        if ($usuarioExistente) {

            // 🚫 CPF pertence ao Super Master
            if ($usuarioExistente->is_super_master && !$auth->is_super_master) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Este CPF pertence ao Super Master e não pode ser usado para criar novos vínculos.')
                    ->with('usuario_existente', null);
            }

            // 🚫 CPF pertence a um Master
            if ($usuarioExistente->roles->pluck('role_name')->contains('master') && !$auth->is_super_master) {
                // Permite apenas se for o próprio Master autenticado
                if ($auth->cpf !== $request->cpf) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Somente o próprio Master ou o Super Master podem criar vínculos com este CPF.')
                        ->with('usuario_existente', null);
                }
            }

            // ✅ CPF já existente, mas permitido — mostra opção de vincular
            return redirect()
                ->back()
                ->withInput()
                ->with('usuario_existente', $usuarioExistente->id);
        }

        // ✅ Criação de novo usuário
        $request->validate([
            'senha' => 'required|string|min:6',
        ]);

        // 🔒 Se tentar criar Super Master e não for Super Master autenticado → bloqueia
        if ($request->filled('roles')) {
            $temSuper = Role::whereIn('id', $request->roles)->where('role_name', 'super_master')->exists();
            if ($temSuper && !$auth->is_super_master) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Apenas o Super Master pode criar outro Super Master.');
            }
        }

        $usuario = Usuario::create([
            'nome_u'     => $request->nome_u,
            'cpf'        => $request->cpf,
            'senha_hash' => Hash::make($request->senha),
            'status'     => 1,
            'school_id'  => $request->school_id,
        ]);

        // 🔗 Vincula roles (com school_id)
        if ($request->filled('roles')) {
            foreach ($request->roles as $role_id) {
                $usuario->roles()->attach($role_id, ['school_id' => $request->school_id]);
            }
        }

        return redirect()
            ->route('master.usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    }
    
    /*
    💡 O que este código evita
    Situação                        Resultado
    CPF pertence ao Super Master    ❌ Ninguém pode criar/vincular, exceto ele mesmo
    CPF pertence a um Master        ❌ Só o próprio master ou o Super Master podem vincular/criar
    CPF pertence a usuário comum    ✅ Permite vincular
    Novo usuário com CPF inédito    ✅ Criação normal
    Tentativa de criar outro Super Master   ❌ Bloqueado para todos, exceto o Super Master autenticado
    */
    public function vincular(Request $request, $usuarioId)
    {
        $usuario = Usuario::findOrFail($usuarioId);
        $auth = auth()->user();

        $request->validate([
            'school_id' => 'required|integer',
            'roles'     => 'array|required'
        ]);

        // 🔒 Proteções ao tentar vincular usuários sensíveis
        if ($usuario->is_super_master && !$auth->is_super_master) {
            return back()->with('error', 'Não é permitido vincular o Super Master a outras escolas.');
        }

        if ($usuario->roles->pluck('role_name')->contains('master') && !$auth->is_super_master) {
            if ($auth->cpf !== $usuario->cpf) {
                return back()->with('error', 'Apenas o próprio Master ou o Super Master podem vincular um usuário Master.');
            }
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
            ->route('master.usuarios.index')
            ->with('success', 'Usuário existente vinculado à escola selecionada!');
    }

  

    /*
    🧭 O que mudou / melhorou
    Caso    O que acontece
    Master comum editando a si mesmo    ✅ Pode mudar nome/CPF/senha, ❌ não pode trocar escola nem status
    Master comum editando usuário normal    ✅ Pode mudar tudo
    Master comum editando outro master  ❌ Bloqueado
    Master comum editando super master  ❌ Bloqueado
    Super master editando qualquer um   ✅ Pode tudo, exceto mudar/desativar outro super master
    Super master editando a si mesmo    ✅ Pode alterar dados, ❌ não pode desativar nem trocar escola
    Usuário comum   ❌ Bloqueado em tudo
    */
    public function edit(Usuario $usuario)
    {
        $auth = auth()->user();

        /*
        💡 Explicação prática
        Situação                           Pode editar?    Motivo
        Super Master → a si mesmo          ✅              dono da conta
        Super Master → outro Super Master  🚫              protegido
        Super Master → qualquer outro      ✅              autoridade total
        Master comum → a si mesmo          ✅              pode editar seus dados pessoais
        Master comum → outro master        🚫              proibido
        Master comum → usuário normal      ✅              permitido
        Usuário normal → qualquer um       🚫              sem permissão
        */

        // 🔒 1. Super Master só pode ser editado por ele mesmo
        if ($usuario->is_super_master && $auth->id !== $usuario->id) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Apenas o próprio Super Master pode editar sua conta.');
        }

        // 🔒 2. Master comum não pode editar outro master (só a si mesmo ou usuários normais)
        if ($auth->hasRole('master') && !$auth->is_super_master) {
            if ($auth->id !== $usuario->id && $usuario->roles->pluck('role_name')->contains('master')) {
                return redirect()
                    ->route('master.usuarios.index')
                    ->with('error', 'Você não pode editar outro usuário Master.');
            }
        }

        // 🔒 3. Usuário comum não pode editar ninguém (nem outros nem a si mesmo)
        if (!$auth->hasRole('master') && !$auth->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não tem permissão para editar este usuário.');
        }

        // ✅ Autorizado (edição própria ou com permissão)
        $escolas = Escola::all();
        $roles   = Role::all();
        $rolesUsuario = $usuario->roles->pluck('id')->toArray();

        return view('master.usuarios.edit', compact('usuario', 'escolas', 'roles', 'rolesUsuario'));
    }


    public function update(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();

        /*
        💡 Regras gerais aplicadas também no backend:
        - Super Master nunca pode ser desativado nem mudar de escola
        - Super Master pode editar todos, menos outro super master
        - Master comum pode editar a si mesmo (dados pessoais)
        - Master comum pode editar usuários normais
        - Master comum não pode editar outro master
        - Usuário normal não pode editar ninguém
        */

        // 🔒 1. Usuário comum não pode atualizar ninguém
        if (!$auth->hasRole('master') && !$auth->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não tem permissão para atualizar este usuário.');
        }

        // 🔒 2. Super Master só pode ser atualizado por ele mesmo
        if ($usuario->is_super_master && $auth->id !== $usuario->id && !$auth->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Apenas o Super Master pode editar a conta Super Master.');
        }

        // 🔒 3. Master comum não pode atualizar outro master
        if ($auth->hasRole('master') && !$auth->is_super_master && $usuario->roles->pluck('role_name')->contains('master') && $auth->id !== $usuario->id) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não pode atualizar outro usuário Master.');
        }

        // 🔒 4. Master comum editando a si mesmo → não pode mudar status nem escola
        if ($auth->hasRole('master') && !$auth->is_super_master && $auth->id === $usuario->id) {
            $validated = $request->validate([
                'nome_u' => 'required|string|max:100',
                'cpf'    => 'required|string|max:20',
                'senha'  => 'nullable|string|min:6',
            ]);

            $usuario->update([
                'nome_u' => $validated['nome_u'],
                'cpf'    => $validated['cpf'],
            ]);

            if ($request->filled('senha')) {
                $usuario->update(['senha_hash' => Hash::make($request->senha)]);
            }

            return redirect()
                ->route('master.usuarios.index')
                ->with('success', 'Seus dados foram atualizados (status e escola não podem ser alterados).');
        }

        // 🔒 5. Super Master nunca pode ser desativado nem trocar de escola
        if ($usuario->is_super_master) {
            $request->merge([
                'status' => 1,
                'school_id' => $usuario->school_id,
            ]);
        }

        // 🔓 6. Demais casos (Super Master logado ou Master editando usuário comum)
        $validated = $request->validate([
            'nome_u'    => 'required|string|max:100',
            'cpf'       => 'required|string|max:20',
            'school_id' => 'required|integer',
            'status'    => 'required|in:0,1',
            'senha'     => 'nullable|string|min:6',
        ]);

        $usuario->update([
            'nome_u'    => $validated['nome_u'],
            'cpf'       => $validated['cpf'],
            'school_id' => $validated['school_id'],
            'status'    => $validated['status'],
        ]);

        if ($request->filled('senha')) {
            $usuario->update(['senha_hash' => Hash::make($request->senha)]);
        }

        return redirect()
            ->route('master.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }


    /*
    💡 Explicação resumida das proteções
    Cenário Regra aplicada
    Super Master (ele mesmo)    ✅ Pode adicionar/remover qualquer role, exceto “master”
    Super Master (outros usuários)  ✅ Pode adicionar/remover qualquer role
    Master comum (ele mesmo)    ✅ Pode adicionar/remover qualquer role, exceto “master”
    Master comum (outros usuários)  ❌ Não pode alterar Super Master nem outros Masters
    Usuário comum   ❌ Nenhuma permissão para alterar roles
    */
    public function updateRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();

        $request->validate([
            'school_id' => 'required|integer',
            'roles'     => 'array'
        ]);

        $schoolId = $request->school_id;
        $novasRoles = $request->input('roles', []);

        // 🔍 Busca vínculos antigos
        $vinculosAntigos = $usuario->roles()
            ->wherePivot('school_id', $schoolId)
            ->pluck('syrios_role.id')
            ->toArray();

        $paraAdicionar = array_diff($novasRoles, $vinculosAntigos);
        $paraRemover   = array_diff($vinculosAntigos, $novasRoles);

        // 🧭 Identifica o ID da role "master"
        $roleMasterId = \App\Models\Role::where('role_name', 'master')->value('id');

        /*
        ===========================================
        🔒 1) SUPER MASTER
        ===========================================
        */
        if ($usuario->is_super_master) {
            // Super Master nunca pode perder a role master
            if ($roleMasterId && in_array($roleMasterId, $paraRemover)) {
                $paraRemover = array_diff($paraRemover, [$roleMasterId]);
                session()->flash('warning', 'A role "master" não pode ser removida do Super Master.');
            }
        }

        /*
        ===========================================
        🔒 2) MASTER COMUM
        ===========================================
        */
        if ($auth->hasRole('master') && !$auth->is_super_master) {

            // Master comum não pode alterar roles do Super Master
            if ($usuario->is_super_master) {
                return back()->with('error', 'Você não pode alterar roles do Super Master.');
            }

            // Master comum não pode alterar roles de outro Master
            if ($usuario->roles->pluck('role_name')->contains('master') && $auth->id !== $usuario->id) {
                return back()->with('error', 'Você não pode alterar roles de outro usuário Master.');
            }

            // Master comum não pode remover sua própria role master
            if ($auth->id === $usuario->id && $roleMasterId && in_array($roleMasterId, $paraRemover)) {
                $paraRemover = array_diff($paraRemover, [$roleMasterId]);
                session()->flash('warning', 'Você não pode remover sua própria role Master.');
            }
        }

        /*
        ===========================================
        🔒 3) USUÁRIO COMUM (sem privilégios master)
        ===========================================
        */
        if (!$auth->hasRole('master') && !$auth->is_super_master) {
            return back()->with('error', 'Você não tem permissão para alterar roles.');
        }

        /*
        ===========================================
        ✅ 4) PROCESSA ADIÇÕES E REMOÇÕES
        ===========================================
        */
        foreach ($paraAdicionar as $roleId) {
            try {
                $usuario->roles()->attach($roleId, ['school_id' => $schoolId]);
            } catch (\Throwable $e) {
                return back()->with('error', "Não foi possível adicionar a role (ID $roleId): {$e->getMessage()}");
            }
        }

        foreach ($paraRemover as $roleId) {
            try {
                $usuario->roles()->wherePivot('school_id', $schoolId)->detach($roleId);
            } catch (\Throwable $e) {
                return back()->with('error', "Não foi possível remover a role (ID $roleId): {$e->getMessage()}");
            }
        }

        return back()->with('success', 'Roles atualizadas com sucesso!');
    }


    public function editRoles(Request $request, Usuario $usuario)
    {
        $auth = auth()->user();

        // ===========================================
        // 🔒 REGRAS DE PERMISSÃO
        // ===========================================

        // 1️⃣ Usuário comum nunca pode acessar
        if (!$auth->hasRole('master') && !$auth->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não tem permissão para gerenciar roles.');
        }

        // 2️⃣ Master comum não pode acessar roles do Super Master
        if ($auth->hasRole('master') && !$auth->is_super_master && $usuario->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não pode gerenciar roles do Super Master.');
        }

        // 3️⃣ Master comum não pode acessar roles de outro Master
        if ($auth->hasRole('master') && !$auth->is_super_master &&
            $usuario->roles->pluck('role_name')->contains('master') &&
            $auth->id !== $usuario->id) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não pode gerenciar roles de outro usuário Master.');
        }

        // ===========================================
        // ✅ DADOS PARA A VIEW
        // ===========================================
        $escolas = Escola::all();
        $roles   = Role::all();

        $schoolIdSelecionada = $request->input('school_id');

        $rolesSelecionadas = [];
        if ($schoolIdSelecionada) {
            $rolesSelecionadas = $usuario->roles()
                ->wherePivot('school_id', $schoolIdSelecionada)
                ->pluck('syrios_role.id')
                ->toArray();
        }

        return view('master.usuarios.roles', compact(
            'usuario',
            'escolas',
            'roles',
            'schoolIdSelecionada',
            'rolesSelecionadas'
        ));
    }

    public function confirmDestroy(Usuario $usuario)
    {
        // ⚙️ Coleta vínculos diretos que impedem exclusão
        $vinculos = [
            'professor'   => \DB::table('syrios_professor')->where('usuario_id', $usuario->id)->count(),
            'notificacao' => \DB::table('syrios_notificacao')->where('usuario_id', $usuario->id)->count(),
            'sessao'      => \DB::table('syrios_sessao')->where('usuario_id', $usuario->id)->count(),
            'roles'       => \DB::table('syrios_usuario_role')->where('usuario_id', $usuario->id)->count(),
        ];

        // 🏫 Lista de escolas vinculadas (por roles e/ou professor)
        $escolasRoles = \DB::table('syrios_usuario_role as ur')
            ->join('syrios_escola as e', 'e.id', '=', 'ur.school_id')
            ->where('ur.usuario_id', $usuario->id)
            ->select('e.id', 'e.nome_e', 'e.is_master')
            ->distinct();

        $escolasProfessor = \DB::table('syrios_professor as p')
            ->join('syrios_escola as e', 'e.id', '=', 'p.school_id')
            ->where('p.usuario_id', $usuario->id)
            ->select('e.id', 'e.nome_e', 'e.is_master')
            ->distinct();

        // Une os resultados das duas fontes e remove duplicatas
        $escolasVinculadas = $escolasRoles
            ->union($escolasProfessor)
            ->get();


        return view('master.usuarios.confirm_destroy', compact('usuario', 'vinculos', 'escolasVinculadas'));
    }

    public function destroy(Usuario $usuario)
    {
        
        $auth = auth()->user();

        // 🚫 regra:Impede excluir a si mesmo
        if ($auth && $auth->id === $usuario->id) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Você não pode excluir sua própria conta.');
        }

        // 🔒 regra:Impede excluir o Super Master (a menos que seja o próprio super_master)
        if ($usuario->is_super_master && !$auth->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Apenas o Super Master pode excluir outro Super Master.');
        }

        // 🔒 Impede que um Master comum exclua outro Master
        if ($usuario->roles->pluck('role_name')->contains('master') && !$auth->is_super_master) {
            return redirect()
                ->route('master.usuarios.index')
                ->with('error', 'Apenas o Super Master pode excluir outro usuário Master.');
        }

        // if ($usuario->is_super_master) {
        //     return redirect()
        //         ->route('master.usuarios.index')
        //         ->with('error', 'O usuário master não pode ser excluído.');
        // }


        try {
            // Remove vínculos da pivot
            $usuario->roles()->detach();

            $usuario->delete();

            return redirect()->route('master.usuarios.index')
                ->with('success', 'Usuário excluído com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()
                    ->with('error', 'Não foi possível excluir o usuário. Existem registros vinculados.');
            }

            return redirect()->back()
                ->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }
    }

}
