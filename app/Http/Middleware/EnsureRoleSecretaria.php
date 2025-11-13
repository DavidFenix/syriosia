<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRoleSecretaria
{
    public function handle(Request $request, Closure $next)
    {
        $roleAtual = session('current_role');

        if ($roleAtual !== 'secretaria') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Acesso restrito: apenas a SECRETARIA pode acessar este módulo.');
        }

        return $next($request);
    }
}
