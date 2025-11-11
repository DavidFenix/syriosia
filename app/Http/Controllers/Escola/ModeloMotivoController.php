<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use App\Models\ModeloMotivo;
use Illuminate\Http\Request;

class ModeloMotivoController extends Controller
{
    
    // ✅ Resumo final
    // Ação             Permitida?  
    // Criar            ✅ Sempre    
    // Editar           ✅ Sempre    
    // Excluir          ⚠️ Só se não houver vínculos    
    // Ver (professor)  ✅ Somente leitura   
    // Link no menu da escola  /escola/motivos

    public function index()
    {
        $schoolId = session('current_school_id');
        $motivos = ModeloMotivo::where('school_id', $schoolId)
            ->orderBy('categoria')
            ->orderBy('descricao')
            ->paginate(15);

        return view('escola.motivos.index', compact('motivos'));
    }

    public function create()
    {
        return view('escola.motivos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
        ]);

        ModeloMotivo::create([
            'school_id' => session('current_school_id'),
            'descricao' => $request->descricao,
            'categoria' => $request->categoria,
        ]);

        return redirect()->route('escola.motivos.index')
            ->with('success', '✅ Motivo criado com sucesso!');
    }

    public function edit($id)
    {
        $motivo = ModeloMotivo::where('school_id', session('current_school_id'))
            ->findOrFail($id);

        return view('escola.motivos.edit', compact('motivo'));
    }

    public function update(Request $request, $id)
    {
        $motivo = ModeloMotivo::where('school_id', session('current_school_id'))
            ->findOrFail($id);

        $request->validate([
            'descricao' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
        ]);

        $motivo->update($request->only('descricao', 'categoria'));

        return redirect()->route('escola.motivos.index')
            ->with('success', '📝 Motivo atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $motivo = ModeloMotivo::where('school_id', session('current_school_id'))
            ->findOrFail($id);

        // 🔍 Verifica se há ocorrências associadas
        $temUso = \DB::table(prefix('ocorrencia_motivo'))
            ->where('modelo_motivo_id', $motivo->id)
            ->exists();

        if ($temUso) {
            return back()->with('error', '❌ Este motivo já está sendo usado em uma ocorrência e não pode ser excluído.');
        }

        $motivo->delete();

        return back()->with('success', '🗑 Motivo excluído com sucesso!');
    }

    public function importar()
    {
        $schoolId = session('current_school_id');

        // Motivos de outras escolas
        $motivosOutros = \App\Models\ModeloMotivo::with('escola:id,nome_e')
            ->where('school_id', '!=', $schoolId)
            ->orderBy('descricao')
            ->get();

        return view('escola.motivos.importar', compact('motivosOutros'));
    }

    public function importarSalvar(Request $request)
    {
        $schoolId = session('current_school_id');

        $request->validate([
            'motivos' => 'required|array|min:1'
        ]);

        $motivosSelecionados = \App\Models\ModeloMotivo::whereIn('id', $request->motivos)->get();

        foreach ($motivosSelecionados as $motivo) {
            // Evita duplicação
            $existe = \App\Models\ModeloMotivo::where('school_id', $schoolId)
                ->where('descricao', $motivo->descricao)
                ->exists();

            if (!$existe) {
                \App\Models\ModeloMotivo::create([
                    'school_id' => $schoolId,
                    'descricao' => $motivo->descricao,
                    'categoria' => $motivo->categoria,
                ]);
            }
        }

        return redirect()
            ->route('escola.motivos.index')
            ->with('success', '✅ Motivos importados com sucesso!');
    }


}
