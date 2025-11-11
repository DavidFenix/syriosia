<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use App\Models\Disciplina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisciplinaController extends Controller
{
   
    public function index()
    {
        $schoolId = session('current_school_id');

        // Carrega disciplinas com a escola associada
        $disciplinas = Disciplina::with('escola')
            ->where('school_id', $schoolId)
            ->orderBy('descr_d')
            ->get();

        return view('escola.disciplinas.index', compact('disciplinas'));
    }


    public function create()
    {
        return view('escola.disciplinas.create');
    }

    public function store(Request $request)
    {
        //$escola = Auth::user()->escola;
        $schoolId = session('current_school_id');

        $request->validate([
            'abr'     => 'required|string|max:10',
            'descr_d' => 'required|string|max:100',
        ]);

        Disciplina::create([
            'abr'       => $request->abr,
            'descr_d'   => $request->descr_d,
            'school_id' => $schoolId,
        ]);

        return redirect()->route('escola.disciplinas.index')->with('success', 'Disciplina criada!');
    }

    public function edit($id)
    {
        $schoolId = session('current_school_id');
        $disciplina = Disciplina::where('school_id', $schoolId)->findOrFail($id);

        return view('escola.disciplinas.edit', compact('disciplina'));
    }

    public function update(Request $request, $id)
    {
        $schoolId = session('current_school_id');
        $disciplina = Disciplina::where('school_id', $schoolId)->findOrFail($id);

        $request->validate([
            'abr'     => 'required|string|max:10',
            'descr_d' => 'required|string|max:100',
        ]);

        $disciplina->update($request->only(['abr','descr_d']));

        return redirect()->route('escola.disciplinas.index')->with('success','Disciplina atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $schoolId = session('current_school_id');
        $disciplina = Disciplina::where('school_id', $schoolId)->findOrFail($id);
        $disciplina->delete();

        return redirect()->route('escola.disciplinas.index')->with('success','Disciplina removida!');
    }
    
}
