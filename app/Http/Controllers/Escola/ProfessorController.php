<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use App\Models\Professor;
use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Throwable;


class ProfessorController extends Controller
{
    
    public function index()
    {
        $schoolId = (int) session('current_school_id');

        // 🔹 Role "professor"
        $roleProfessorId = Role::where('role_name', 'professor')->value('id');

        // dd(
        //     Usuario::whereHas('roles', function($q) use ($roleProfessorId, $schoolId) {
        //         $q->where(prefix('usuario_role').'.role_id', $roleProfessorId)
        //           ->where(prefix('usuario_role').'.school_id', $schoolId);
        //     })->toSql()
        // );


        // sql_dump(
        //     Usuario::whereHas('roles', function($q) use ($roleProfessorId, $schoolId) {
        //         $q->where(prefix('usuario_role').'.role_id', $roleProfessorId)
        //           ->where(prefix('usuario_role').'.school_id', $schoolId);
        //     })
        // );


        // 🔹 Busca todos os usuários com role "professor" nesta escola
        $usuariosComRole = Usuario::whereHas('roles', function($q) use ($roleProfessorId, $schoolId) {
            $q->where(prefix('usuario_role').'.role_id', $roleProfessorId)
              ->where(prefix('usuario_role').'.school_id', $schoolId);
        })->get();


        // 🔹 Lista de IDs com role (deve estar em syrios_professor)
        $idsComRole = $usuariosComRole->pluck('id')->toArray();

        // 🔹 Lista de professores atualmente registrados na escola
        $professoresExistentes = Professor::where('school_id', $schoolId)->get();

        $idsAtuais = $professoresExistentes->pluck('usuario_id')->toArray();

        $sincronizados = 0;
        $removidos = 0;

        DB::beginTransaction();
        try {
            // ✅ 1) Adicionar professores faltantes
            foreach ($usuariosComRole as $usuario) {
                if (!in_array($usuario->id, $idsAtuais)) {
                    Professor::create([
                        'usuario_id' => $usuario->id,
                        'school_id'  => $schoolId,
                    ]);
                    $sincronizados++;
                }
            }

            // ✅ 2) Remover professores que perderam a role "professor"
            foreach ($professoresExistentes as $professor) {
                if (!in_array($professor->usuario_id, $idsComRole)) {
                    // Verifica dependências (turmas, ocorrências etc.)
                    $temDependencias = DB::table(prefix('oferta'))
                            ->where('professor_id', $professor->id)->exists()
                        || DB::table(prefix('diretor_turma'))
                            ->where('professor_id', $professor->id)->exists()
                        || DB::table(prefix('ocorrencia'))
                            ->where('professor_id', $professor->id)->exists();

                    if ($temDependencias) {
                        continue; // ❗ Pula a exclusão se houver vínculos
                    }

                    $professor->delete();
                    $removidos++;
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao sincronizar professores: '.$e->getMessage());
        }

        // 🔹 Carrega professores com suas escolas e usuários
        $professores = Professor::with(['usuario.escola'])
            ->where('school_id', $schoolId)
            ->get();

        // 🔹 Mensagens de resultado
        $mensagens = [];
        if ($sincronizados > 0) {
            $mensagens[] = "✅ $sincronizados novo(s) professor(es) sincronizado(s).";
        }
        if ($removidos > 0) {
            $mensagens[] = "🗑 $removidos professor(es) removido(s) por perda de vínculo.";
        }

        $mensagem = !empty($mensagens)
            ? implode(' ', $mensagens)
            : 'Lista Atualizada!';

        return view('escola.professores.index', compact('professores', 'mensagem'));
    }


    public function create()
    {
        return view('escola.professores.create');
    }

    public function store(Request $request)
    {
        $schoolId = session('current_school_id');

        $request->validate([
            'usuario_id' => 'required|integer',
        ]);

        Professor::create([
            'usuario_id' => $request->usuario_id,
            'school_id'  => $schoolId,
        ]);

        return redirect()->route('escola.professores.index')->with('success', 'Professor criado!');
    }

    public function edit(Professor $professor)
    {
        return view('escola.professores.edit', compact('professor'));
    }

    public function update(Request $request, Professor $professor)
    {
        $request->validate([
            'usuario_id' => 'required|integer',
        ]);

        $professor->update($request->only('usuario_id'));

        return redirect()->route('escola.professores.index')->with('success', 'Professor atualizado!');
    }

    public function destroy($id)
{
    $schoolId = (int) session('current_school_id');
    $auth     = auth()->user();

    try {
        DB::beginTransaction();

        $professor = Professor::where('school_id', $schoolId)
            ->where('id', $id)
            ->firstOrFail();

        $usuarioId = (int) $professor->usuario_id;
        $isSelf = ($auth->id === $usuarioId);

        // Confirma se gestor tem permissão
        $authTemRoleEscola = $auth->roles()
            ->wherePivot('school_id', $schoolId)
            ->where('role_name', 'escola')
            ->exists();

        if (!$authTemRoleEscola) {
            return back()->with('error', 'Apenas gestores escolares podem remover professores.');
        }

        // Verifica dependências
        $temDependencias = DB::table(prefix('oferta'))
                ->where('professor_id', $professor->id)->exists()
            || DB::table(prefix('diretor_turma'))
                ->where('professor_id', $professor->id)->exists()
            || DB::table(prefix('ocorrencia'))
                ->where('professor_id', $professor->id)->exists();

        if ($temDependencias) {
            DB::rollBack();
            return back()->with('error', 'Não é possível remover este professor: há vínculos ativos.');
        }

        // Remove vínculo da pivot syrios_usuario_role (assim não será recriado)
        $roleProfessorId = Role::where('role_name', 'professor')->value('id');
        if ($roleProfessorId) {
            DB::table(prefix('usuario_role'))
                ->where('usuario_id', $usuarioId)
                ->where('school_id', $schoolId)
                ->where('role_id', $roleProfessorId)
                ->delete();
        }

        // Exclui registro da tabela professor
        $professor->delete();

        DB::commit();

        $mensagem = $isSelf
            ? 'Você se removeu da lista de professores desta escola.'
            : 'Professor removido com sucesso.';

        return redirect()->route('escola.professores.index')->with('success', $mensagem);

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', 'Erro ao excluir: '.$e->getMessage());
    }
}


}
