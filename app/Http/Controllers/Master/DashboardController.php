<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use App\Models\Usuario;
use App\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        // todas as escolas
        //$escolas = Escola::all();
        $filtro = request('tipo'); 
        $escolas = Escola::with('mae')->filtrar($filtro)->get();

        // todos os usuários já com escola e roles carregados
        //$usuarios = Usuario::with(['escola', 'roles'])->get();
        $usuarios = Usuario::with(['escola','roles'])->filtrarPorEscola($filtro)->get();

        // todas as roles
        $roles = Role::all();

        // filtro padrão (para compatibilidade com index.blade.php de escolas)
        $filtro = null;

        /**
         * 🔹 Parte extra para associações
         */
        // escolas mãe (secretaria_id = NULL)
        $escolasMae = Escola::whereNull('secretaria_id')->get();

        // verifica se veio um filtro ?mae_id na URL
        $maeSelecionada = request('mae_id');

        $escolasFilhas = collect();
        $nomeMae = null;

        if ($maeSelecionada) {
            $mae = Escola::find($maeSelecionada);
            if ($mae) {
                $nomeMae = $mae->nome_e;
                $escolasFilhas = $mae->filhas; // usa relacionamento já definido no Model
            }
        }

        return view('master.dashboard', compact(
            'escolas', 
            'usuarios', 
            'roles', 
            'filtro',
            'escolasMae',
            'maeSelecionada',
            'escolasFilhas',
            'nomeMae'
        ));
    }


}
