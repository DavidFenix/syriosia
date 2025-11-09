<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Mostrar formulário de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Processar login
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => 'required',
            'senha' => 'required',
        ]);

        // Busca usuário pelo CPF
        $usuario = Usuario::where('cpf', $request->cpf)->first();

        if (!$usuario || !Hash::check($request->senha, $usuario->senha_hash)) {
            return back()->withErrors(['cpf' => 'Credenciais inválidas.']);
        }

        // Loga usuário manualmente
        Auth::login($usuario);

        return redirect()->route('master.dashboard')
                         ->with('success', 'Login realizado com sucesso!');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                         ->with('success', 'Você saiu com segurança.');
    }
}
