<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Escola;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['cpf' => $credentials['cpf'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Carrega roles + school_id do pivot
            $roles = $user->roles()->withPivot('school_id')->get();

            if ($roles->isEmpty()) {
                Auth::logout();
                return back()->withErrors(['cpf' => 'Usuário sem vínculos ativos.']);
            }

            // 👉 Caso 1: Apenas 1 vínculo (escola+role) → entra direto
            if ($roles->count() === 1) {
                $role = $roles->first();
                $this->setContext($role->pivot->school_id, $role->role_name);
                return redirect()->to($this->dashboardRoute($role->role_name));
            }

            // 👉 Caso 2: Mais de um vínculo → tela de escolha
            return redirect()->route('choose.school');
        }

        return back()->withErrors([
            'cpf' => 'As credenciais não conferem.',
        ])->onlyInput('cpf');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // limpar sessão toda
        $request->session()->forget(['current_school_id','current_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // 👉 Helpers
    protected function setContext($schoolId, $roleName)
    {
        session([
            'current_school_id' => $schoolId,
            'current_role'      => $roleName,
        ]);
    }

    protected function dashboardRoute($roleName)
    {
        return match($roleName) {
            'master'     => route('master.dashboard'),
            'secretaria' => route('secretaria.dashboard'),
            'escola'     => route('escola.dashboard'),
            'professor'  => route('professor.dashboard'),
            default      => '/',
        };
    }

    // 👉 Telas de escolha
    public function chooseSchool()
    {
        $user = auth()->user();

        // Agrupa roles por escola
        $roles = $user->roles()->withPivot('school_id')->get();
        $escolas = $roles->groupBy('pivot.school_id');

        return view('auth.choose_school', compact('escolas'));
    }

    public function chooseRole($schoolId)
    {
        $roles = auth()->user()->roles()->wherePivot('school_id', $schoolId)->get();
        $escola = Escola::findOrFail($schoolId);

        return view('auth.choose_role', compact('roles','schoolId','escola'));
    }

    public function setContextPost(Request $request)
    {
        $request->validate([
            'school_id' => 'required|integer',
            'role_name' => 'required|string',
        ]);

        $this->setContext($request->school_id, $request->role_name);

        return redirect()->to($this->dashboardRoute($request->role_name));
    }
}
