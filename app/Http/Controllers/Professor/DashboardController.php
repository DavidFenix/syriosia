<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Oferta;
use App\Models\Ocorrencia;

class DashboardController extends Controller
{
    
    public function index()
    {
        $usuario = Auth::user();
        $schoolId = session('current_school_id');
        $ano = session('ano_letivo_atual') ?? date('Y');

        // 🧩 Garante que o usuário realmente tenha vínculo com professor
        $professor = $usuario->professor;
        if (!$professor) {
            abort(403, 'Usuário atual não está vinculado a um professor.');
        }

        $professorId = $professor->id;

        // ✅ Total de ofertas (disciplinas e turmas)
        $totalOfertas = Oferta::where('professor_id', $professorId)
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $ano)
            ->count();

        // ✅ Total de ocorrências aplicadas (vigentes no ano atual)
        $totalOcorrencias = Ocorrencia::where('professor_id', $professorId)
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $ano)
            ->count();

        // ✅ Ocorrências ativas (vigentes e não arquivadas)
        $ocorrenciasAtivas = Ocorrencia::where('professor_id', $professorId)
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $ano)
            ->where('status', 1)
            ->count();

        // ✅ Ocorrências arquivadas (vigentes, status = 0)
        $ocorrenciasArquivadas = Ocorrencia::where('professor_id', $professorId)
            ->where('school_id', $schoolId)
            ->where('ano_letivo', $ano)
            ->where('status', 0)
            ->count();

        return view('professor.dashboard', compact(
            'usuario',
            'totalOfertas',
            'totalOcorrencias',
            'ocorrenciasAtivas',
            'ocorrenciasArquivadas',
            'ano'
        ));
    }

}
