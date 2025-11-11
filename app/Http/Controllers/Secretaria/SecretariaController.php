<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Escola;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class SecretariaController extends Controller
{
    public function dashboard()
    {
        $usuario = Auth::user();

        // pega a escola vinculada ao usuário (que é uma secretaria)
        $secretaria = $usuario->escola;

        // pega apenas as escolas filhas dessa secretaria
        $escolasFilhas = Escola::where('secretaria_id', $secretaria->id)->get();

        // pega apenas os usuários que pertencem a essa secretaria e suas filhas
        $usuarios = Usuario::whereIn('school_id', $escolasFilhas->pluck('id')->push($secretaria->id))
                           ->with(['escola','roles'])
                           ->get();

        return view('secretaria.dashboard', compact('secretaria','escolasFilhas','usuarios'));
    }

    public function index()
    {
        // secretaria logada
        $secretaria = auth()->user()->escola;

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada a este usuário.');
        }

        // lista apenas as escolas filhas dessa secretaria
        $escolas = $secretaria->filhas()->get();

        return view('secretaria.escolas.index', compact('escolas','secretaria'));
    }

    public function create()
    {
        $secretaria = auth()->user()->escola;

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada a este usuário.');
        }

        return view('secretaria.escolas.create', compact('secretaria'));
    }

    public function store(Request $request)
    {
        $secretaria = auth()->user()->escola;

        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada a este usuário.');
        }

        $validated = $request->validate([
            'nome_e' => 'required|string|max:150',
            'inep'   => 'nullable|string|max:20|unique:syrios_escola,inep',
            'cnpj'   => 'nullable|string|max:20|unique:syrios_escola,cnpj',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
        ]);

        $validated['secretaria_id'] = $secretaria->id;

        Escola::create($validated);

        return redirect()->route('secretaria.escolas.index')
            ->with('success', 'Escola criada com sucesso.');
    }

    public function edit(Escola $escola)
    {
        $secretaria = auth()->user()->escola;

        if (!$secretaria || $escola->secretaria_id !== $secretaria->id) {
            return redirect()->route('secretaria.escolas.index')
                ->with('error', 'Você não pode editar esta escola.');
        }

        return view('secretaria.escolas.edit', compact('escola','secretaria'));
    }

    public function update(Request $request, Escola $escola)
    {
        $secretaria = auth()->user()->escola;

        if (!$secretaria || $escola->secretaria_id !== $secretaria->id) {
            return redirect()->route('secretaria.escolas.index')
                ->with('error', 'Você não pode atualizar esta escola.');
        }

        $validated = $request->validate([
            'nome_e' => 'required|string|max:150',
            'inep'   => 'nullable|string|max:20|unique:syrios_escola,inep,' . $escola->id,
            'cnpj'   => 'nullable|string|max:20|unique:syrios_escola,cnpj,' . $escola->id,
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
        ]);

        $escola->update($validated);

        return redirect()->route('secretaria.escolas.index')
            ->with('success', 'Escola atualizada com sucesso.');
    }

    public function destroy(Escola $escola)
    {
        $secretaria = auth()->user()->escola;

        if (!$secretaria || $escola->secretaria_id !== $secretaria->id) {
            return redirect()->route('secretaria.escolas.index')
                ->with('error', 'Você não pode excluir esta escola.');
        }

        $escola->delete();

        return redirect()->route('secretaria.escolas.index')
            ->with('success', 'Escola excluída com sucesso.');
    }
}

