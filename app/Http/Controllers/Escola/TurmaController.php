<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TurmaController extends Controller
{
    public function index()
    {
        // $escola = Auth::user()->escola;
        // $turmas = Turma::where('school_id', $escola->id)->get();
        $schoolId = session('current_school_id');
        $turmas = Turma::where('school_id', $schoolId)->get();

        //return view('escola.turmas.index', compact('turmas', 'escola'));
        return view('escola.turmas.index', compact('turmas'));
    }

    public function create()
    {
        return view('escola.turmas.create');
    }

    public function store(Request $request)
    {
        //$escola = Auth::user()->escola;
        $schoolId = session('current_school_id');

        $request->validate([
            'serie_turma' => 'required|string|max:20',
            'turno'       => 'required|string|max:20',
        ]);

        Turma::create([
            'serie_turma' => $request->serie_turma,
            'turno'       => $request->turno,
            'school_id'   => $schoolId,
        ]);

        return redirect()->route('escola.turmas.index')->with('success', 'Turma criada!');
    }

    public function edit($id)
    {
        $schoolId = session('current_school_id');
        $turma = Turma::where('school_id', $schoolId)->findOrFail($id);

        return view('escola.turmas.edit', compact('turma'));
    }

    public function update(Request $request, $id)
    {
        $schoolId = session('current_school_id');
        $turma = Turma::where('school_id', $schoolId)->findOrFail($id);

        $request->validate([
            'serie_turma' => 'required|string|max:20',
            'turno'       => 'required|string|max:20',
        ]);

        $turma->update($request->only(['serie_turma','turno']));

        return redirect()->route('escola.turmas.index')->with('success','Turma atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $schoolId = session('current_school_id');
        $turma = Turma::where('school_id', $schoolId)->findOrFail($id);
        $turma->delete();

        return redirect()->route('escola.turmas.index')->with('success','Turma removida!');
    }
   
}
