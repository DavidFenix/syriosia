<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRoleProfessor
{
    public function handle(Request $request, Closure $next)
    {
        $roleAtual = session('current_role');

        if ($roleAtual !== 'professor') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Acesso restrito: apenas usuários com o papel de PROFESSOR podem acessar este módulo.');
        }

        return $next($request);
    }
}
