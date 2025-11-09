<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class EnsureContextSelected
{
    public function handle(Request $request, Closure $next)
    {
        // precisa estar logado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $schoolId = session('current_school_id');
        $roleName = session('current_role');

        // se não há contexto na sessão → força escolher
        if (!$schoolId || !$roleName) {
            return redirect()->route('choose.school');
        }

        // pega role_id pelo nome
        $role = Role::where('role_name', $roleName)->first();
        if (!$role) {
            session()->forget(['current_school_id','current_role']);
            $request->session()->regenerate();

            return redirect()->route('choose.school')
                ->withErrors(['contexto' => 'Papel inválido. Selecione novamente.']);
        }

        // valida se o usuário realmente tem esse vínculo
        $temContexto = $user->roles()
            ->where('role_id', $role->id)
            ->wherePivot('school_id', $schoolId)
            ->exists();

        if (!$temContexto) {
            // limpa e volta para tela de seleção
            session()->forget(['current_school_id','current_role']);
            $request->session()->regenerate();

            return redirect()->route('choose.school')
                ->withErrors(['contexto' => 'Seu vínculo com essa escola/papel não é mais válido.']);
        }

        // segue o fluxo
        return $next($request);
    }
}
