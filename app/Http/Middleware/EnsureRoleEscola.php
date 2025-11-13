<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRoleEscola
{
    public function handle(Request $request, Closure $next)
    {
        $roleAtual = session('current_role');

        if ($roleAtual !== 'escola') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Acesso restrito: apenas usuários com papel ESCOLA podem acessar este módulo.');
        }

        return $next($request);
    }
}
