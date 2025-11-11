<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EscolaController extends Controller
{
    
    public function index()
    {
        // obtém ID da escola atual da sessão
        $currentSchoolId = session('current_school_id');

        // verifica se há uma escola em contexto
        if (!$currentSchoolId) {
            return redirect()->route('home')->with('error', 'Nenhuma escola selecionada no momento.');
        }

        // busca a escola atual no banco
        $secretaria = Escola::find($currentSchoolId);

        // se não existir (por exemplo, ID inválido)
        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Escola atual não encontrada.');
        }

        // filhas da secretaria atual
        $escolas = $secretaria->filhas()->get();

        return view('secretaria.escolas.index', compact('escolas', 'secretaria'));
    }


    /*public function index()
    {
        // secretaria logada
        $secretaria = auth()->user()->escola;

        // se não tiver secretaria vinculada
        if (!$secretaria) {
            return redirect()->route('home')->with('error', 'Nenhuma secretaria vinculada a este usuário.');
        }

        // filhas da secretaria logada
        $escolas = $secretaria->filhas()->get();

        return view('secretaria.escolas.index', compact('escolas','secretaria'));
    }*/

    /*public function index()
    {
        $secretaria = Auth::user()->escola;

        // só filhas da secretaria logada
        $escolas = Escola::where('secretaria_id', $secretaria->id)->get();

        return view('secretaria.escolas.index', compact('secretaria','escolas'));
    }*/

    public function create()
    {
        return view('secretaria.escolas.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $roleAtual = session('current_role'); 
        $schoolId = session('current_school_id'); 

        // 🔒 1. Permissão: apenas usuários com role "secretaria" ou "master" podem criar escolas
        if (!in_array($roleAtual, ['secretaria', 'master'])) {
            return redirect()
                ->route('dashboard')
                ->with('error', '🚫 Você não tem permissão para criar escolas.');
        }

        // 🔒 2. Usa a escola ativa no contexto da sessão como secretaria-mãe
        $secretaria = Escola::find($schoolId);
        if (!$secretaria) {
            return back()->with('error', '❌ Nenhuma escola ativa na sessão.');
        }

        // ✅ 3. Validação dos dados
        $validated = $request->validate([
            'nome_e'   => 'required|string|max:150',
            'inep'     => 'nullable|string|max:20|unique:' . prefix('escola') . ',inep',
            'cnpj'     => 'nullable|string|max:20|unique:' . prefix('escola') . ',cnpj',
            'cidade'   => 'nullable|string|max:100',
            'estado'   => 'nullable|string|max:100',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // ✅ 4. Cria a nova escola filha, vinculada à secretaria ativa da sessão
            $novaEscola = Escola::create([
                'nome_e'        => $validated['nome_e'],
                'inep'          => $validated['inep'] ?? null,
                'cnpj'          => $validated['cnpj'] ?? null,
                'cidade'        => $validated['cidade'] ?? null,
                'estado'        => $validated['estado'] ?? null,
                'endereco'      => $validated['endereco'] ?? null,
                'telefone'      => $validated['telefone'] ?? null,
                'secretaria_id' => $secretaria->id,
                'is_master'     => 0,
            ]);

            DB::commit();

            // 🪵 5. Log opcional para auditoria
            Log::info('Nova escola criada', [
                'usuario_id'     => $user->id,
                'usuario_nome'   => $user->nome_u ?? '(desconhecido)',
                'secretaria_id'  => $secretaria->id,
                'escola_criada'  => $novaEscola->id,
            ]);

            return redirect()
                ->route('secretaria.escolas.index')
                ->with('success', '🏫 Escola criada com sucesso e vinculada à secretaria ativa.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erro ao criar escola', [
                'usuario_id' => $user->id ?? null,
                'mensagem'   => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', '⚠️ Ocorreu um erro ao criar a escola. Verifique se o CNPJ ou INEP já estão cadastrados.');
        }
    }


    // public function store(Request $request)
    // {
        
    //     $user = Auth::user();

    //     // 🔒 1. Permissão: apenas usuários com role "secretaria" podem criar escolas
    //     if (!$user->hasRole('secretaria')) {
    //         return redirect()
    //             ->route('dashboard')
    //             ->with('error', 'Você não tem permissão para criar escolas.');
    //     }

    //     // 🔒 2. Confirma que o usuário pertence a uma escola válida (secretaria)
    //     $secretaria = $user->escola;
    //     if (!$secretaria) {
    //         return back()->with('error', 'Não foi possível identificar a secretaria vinculada ao seu usuário.');
    //     }

    //     // ✅ 3. Validação dos dados
    //     $validated = $request->validate([
    //         'nome_e' => 'required|string|max:150',
    //         'inep'   => 'nullable|string|max:20',
    //         'cnpj'   => 'nullable|string|max:20',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // ✅ 4. Cria a nova escola filha
    //         $novaEscola = Escola::create([
    //             'nome_e'        => $validated['nome_e'],
    //             'inep'          => $validated['inep'] ?? null,
    //             'cnpj'          => $validated['cnpj'] ?? null,
    //             'secretaria_id' => $secretaria->id,
    //         ]);

    //         DB::commit();

    //         // 🪵 5. Log opcional para auditoria
    //         Log::info("Nova escola criada pela secretaria", [
    //             'usuario_id' => $user->id,
    //             'secretaria_id' => $secretaria->id,
    //             'escola_id' => $novaEscola->id,
    //         ]);

    //         return redirect()
    //             ->route('secretaria.escolas.index')
    //             ->with('success', 'Escola criada com sucesso!');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         Log::error('Erro ao criar escola', [
    //             'usuario_id' => $user->id ?? null,
    //             'mensagem'   => $e->getMessage(),
    //         ]);

    //         return back()
    //             ->withInput()
    //             ->with('error', 'Ocorreu um erro ao criar a escola. Talvez já existe uma escola cadastrada com esse CNPJ ou INEP ou são inválidos.');
    //     }
    // }

    /*public function store(Request $request)
    {
        $secretaria = Auth::user()->escola;

        Escola::create([
            'nome_e' => $request->nome_e,
            'inep'   => $request->inep,
            'cnpj'   => $request->cnpj,
            'secretaria_id' => $secretaria->id,
        ]);

        return redirect()->route('secretaria.escolas.index')->with('success','Escola criada');
    }*/

    public function edit(Escola $escola)
    {
        return view('secretaria.escolas.edit', compact('escola'));
    }

    public function update(Request $request, Escola $escola)
    {
        $escola->update($request->only('nome_e','inep','cnpj'));
        return redirect()->route('secretaria.escolas.index')->with('success','Escola atualizada');
    }

    public function destroy(Escola $escola)
    {
        // 🔒 Impede exclusão da escola principal
        if ($escola->is_master) {
            return redirect()->back()->with('error', 'A escola principal não pode ser excluída.');
        }

        // Exclui a escola
        $escola->delete();

        return redirect()->route('secretaria.escolas.index')
            ->with('success', 'Escola excluída com sucesso!');
    }


    // public function destroy(Escola $escola)
    // {
    //     $escola->delete();
    //     return redirect()->route('secretaria.escolas.index')->with('success','Escola excluída');
    // }
}
