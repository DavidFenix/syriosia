<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRoleMaster
{
    public function handle(Request $request, Closure $next)
    {
        $roleAtual = session('current_role');

        if ($roleAtual !== 'master') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Acesso restrito: apenas usuários MASTER podem acessar este módulo.');
        }

        return $next($request);
    }
}
