<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login'); // ou onde preferir
        }

        // pega as roles do usuário
        $userRoles = $user->roles->pluck('role_name')->toArray();

        // se o usuário não tem nenhuma das roles exigidas
        if (!array_intersect($roles, $userRoles)) {
            abort(403, 'Acesso negado');
        }

        return $next($request);
    }
}

