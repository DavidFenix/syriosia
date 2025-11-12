<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Ocorrencia,
    ModeloMotivo,
    Oferta,
    Aluno,
    Escola,
    Professor
};
use Barryvdh\DomPDF\Facade\Pdf;

class OcorrenciaController extends Controller
{
    

    public function index()
    {
        $usuario = auth()->user();
        $prof = $usuario->professor;
        $profId = $prof->id ?? 0;

        /*
        |--------------------------------------------------------------------------
        | 🎯 1. Determina se deve paginar ou mostrar tudo
        |--------------------------------------------------------------------------
        | O valor padrão é 15 registros por página, mas se o usuário clicar em
        | "👁️ Ver tudo", ele enviará ?perPage=9999 na query string.
        */
        $perPage = request()->get('perPage', 25);

        /*
        |--------------------------------------------------------------------------
        | 🧭 2. Coleta as ofertas (disciplinas) das turmas onde o professor é diretor
        |--------------------------------------------------------------------------
        | Assim, o professor vê tanto as ocorrências que ele próprio registrou
        | quanto as das turmas que ele coordena (como diretor de turma).
        */
        $ofertasDasTurmasQueDirijo = DB::table(prefix('oferta'))
            ->whereIn('turma_id', function ($inner) use ($profId) {
                $inner->select('turma_id')
                    ->from(prefix('diretor_turma'))
                    ->where('professor_id', $profId)
                    ->where('vigente', true);
            })
            ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | 📋 3. Busca as ocorrências do professor (autor) ou diretor de turma na escola atual e do ano atual, e apenas dos alunos enturmados na turma onde é DT
        |--------------------------------------------------------------------------
        | Traz dados completos: aluno, professor, oferta (turma + disciplina) e motivos.
        | Ordena da mais recente para a mais antiga.
        */
        $query = Ocorrencia::with([
            'aluno',
            'professor.usuario',
            'oferta.turma',
            'oferta.disciplina',
            'motivos'
        ])
        ->daEscolaAtual()
        ->anoAtual()
        ->where(function ($q) use ($profId) {

            $q->where('professor_id', $profId) // 🔹 Ocorrências aplicadas por ele
              ->orWhereIn('aluno_id', function ($sub) use ($profId) {
                  $sub->select('aluno_id')
                      ->from(prefix('enturmacao') . ' as e')
                      ->join(prefix('diretor_turma') . ' as d', 'd.turma_id', '=', 'e.turma_id')
                      ->where('d.professor_id', $profId)
                      ->where('d.vigente', true);
              });
        })
        ->orderByDesc('created_at');


        //esta consulta ainda mostra as ocorrencias do aluno depois que ele sai da turma onde o professor é DT
        // $query = Ocorrencia::with([
        //     'aluno',
        //     'professor.usuario',
        //     'oferta.turma',
        //     'oferta.disciplina',
        //     'motivos'
        // ])
        // ->daEscolaAtual()   // 🔹 aplica school_id = session('current_school_id')
        // ->anoAtual()        // 🔹 aplica ano_letivo = session('ano_letivo_atual') ou date('Y')
        // ->where(function ($q) use ($profId, $ofertasDasTurmasQueDirijo) {
        //     $q->where('professor_id', $profId)
        //       ->orWhereIn('oferta_id', $ofertasDasTurmasQueDirijo);
        // })
        // ->orderByDesc('created_at');

        //esta consulta inclui turmas de outras escolas o que não deveria aqui
        // $query = Ocorrencia::with([
        //     'aluno',
        //     'professor.usuario',
        //     'oferta.turma',
        //     'oferta.disciplina',
        //     'motivos'
        // ])
        // ->where(function ($q) use ($profId, $ofertasDasTurmasQueDirijo) {
        //     $q->where('professor_id', $profId)
        //       ->orWhereIn('oferta_id', $ofertasDasTurmasQueDirijo);
        // })
        // ->orderByDesc('created_at');


        /*
        |--------------------------------------------------------------------------
        | ⚙️ 4. Decide entre paginação real (Laravel) ou “ver tudo” (DataTables)
        |--------------------------------------------------------------------------
        | Se o usuário clicar em “ver tudo”, ele carrega tudo (get()).
        | Caso contrário, pagina 15 por vez com links.
        */
        $ocorrencias = ($perPage > 25)
            ? $query->get()
            : $query->paginate($perPage);

        /*
        |--------------------------------------------------------------------------
        | 🔐 5. Calcula permissões linha a linha
        |--------------------------------------------------------------------------
        | Cada ocorrência recebe flags: autor, diretor, outro.
        */
        foreach ($ocorrencias as $oc) {
            $per = $this->podeGerenciar($oc, $usuario);
            $oc->is_autor   = $per['autor'];
            $oc->is_diretor = $per['diretor'];
            $oc->is_outro   = $per['outro'];
        }

        /*
        |--------------------------------------------------------------------------
        | 🎨 6. Retorna à view com dados prontos para o Blade
        |--------------------------------------------------------------------------
        */
        return view('professor.ocorrencias.index', compact('ocorrencias'));
    }

    /**
     * Exibe o formulário de encaminhamento (diretor).
     */
    public function encaminhar($id)
    {
        $ocorrencia = Ocorrencia::with(['aluno', 'professor.usuario', 'oferta.turma'])
            ->findOrFail($id);

        $usuario = auth()->user();
        $permissoes = $this->podeGerenciar($ocorrencia, $usuario);

        if (!$permissoes['diretor']) {
            return back()->with('error', 'Você não tem permissão para encaminhar esta ocorrência.');
        }

        return view('professor.ocorrencias.encaminhar', compact('ocorrencia'));
    }

    /**
     * Salva o encaminhamento e atualiza o status.
     */
    public function salvarEncaminhamento(Request $request, $id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);

        $usuario = auth()->user();
        $permissoes = $this->podeGerenciar($ocorrencia, $usuario);

        if (!$permissoes['diretor']) {
            return back()->with('error', 'Apenas o diretor de turma pode arquivar ou encaminhar.');
        }

        $request->validate([
            'status' => 'required|in:0,1,2',
            'encaminhamentos' => 'nullable|string|max:2000',
        ]);

        $ocorrencia->update([
            'status' => $request->status,
            'encaminhamentos' => $request->encaminhamentos,
            'recebido_em' => now(),
        ]);

        return redirect()
            ->route('professor.ocorrencias.show', $ocorrencia->id)
            ->with('success', 'Encaminhamento registrado com sucesso.');
    }



    /**
     * Helper: descobre se o usuário logado é autor da ocorrência,
     * diretor da turma da ocorrência, ou "outro".
     */
    private function podeGerenciar(Ocorrencia $ocorrencia, $usuario): array
    {
        $prof = $usuario->professor; // Usuario -> Professor
        $profId = $prof->id ?? null;

        // Autor: quando o professor_id da ocorrência é o id do professor logado
        $isAutor = $profId && ($ocorrencia->professor_id === $profId);

        // Diretor: é diretor da turma desta ocorrência (se houver oferta/turma)
        $turmaId = $ocorrencia->oferta->turma_id ?? null;
        $isDiretorTurma = false;
        if ($turmaId && $profId) {
            $isDiretorTurma = \App\Models\DiretorTurma::where('professor_id', $profId)
                ->where('turma_id', $turmaId)
                ->where('vigente', true)
                ->exists();
        }

        return [
            'autor'   => (bool) $isAutor,
            'diretor' => (bool) $isDiretorTurma,
            'outro'   => !$isAutor && !$isDiretorTurma,
        ];
    }

    /**
     * Gera PDF do histórico do aluno (versão resumida/bonita).
     */
    public function gerarPdf($alunoId)
    {
        $aluno  = Aluno::findOrFail($alunoId);
        $escola = Escola::find(session('current_school_id'));

        // Turma
        $turma = optional(
            $aluno->enturmacao()->with('turma')->first()
        )->turma;

        /*
        |--------------------------------------------------------------------------
        | 1. LOGO DA ESCOLA — detectando PNG/JPG automaticamente
        |--------------------------------------------------------------------------
        */
        $logoBase = public_path("storage/logos/");
        $logoName = $escola->logo_path ?? '';

        $possiveisExt = [
            $logoName,
            pathinfo($logoName, PATHINFO_FILENAME).'.jpg',
            pathinfo($logoName, PATHINFO_FILENAME).'.jpeg',
            pathinfo($logoName, PATHINFO_FILENAME).'.png',
        ];

        $logoFile = null;

        foreach ($possiveisExt as $f) {
            if ($f && file_exists($logoBase.$f)) {
                $logoFile = $logoBase.$f;
                break;
            }
        }

        if (!$logoFile) {
            $logoFile = $logoBase.'padrao.jpg'; // mantenha em JPG
        }

        /*
        |--------------------------------------------------------------------------
        | 2. FOTO DO ALUNO — idem
        |--------------------------------------------------------------------------
        */
        $fotoBase = public_path("storage/img-user/");
        $mat = $aluno->matricula;
        $school = $aluno->school_id;

        // Lista de possíveis nomes de arquivo
        $possiveisFotos = [
            "{$school}_{$mat}.jpg",
            "{$school}_{$mat}.jpeg",
            "{$school}_{$mat}.png",
            "{$mat}.jpg",
            "{$mat}.jpeg",
            "{$mat}.png",
        ];

        $fotoFile = null;

        foreach ($possiveisFotos as $f) {
            if (file_exists($fotoBase.$f)) {
                $fotoFile = $fotoBase.$f;
                break;
            }
        }

        if (!$fotoFile) {
            $fotoFile = $fotoBase.'padrao.jpg';
        }

        /*
        |--------------------------------------------------------------------------
        | Ocorrências
        |--------------------------------------------------------------------------
        */
        $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
            ->where('aluno_id', $aluno->id)
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView('professor.ocorrencias.pdf_historico', [
            'aluno'      => $aluno,
            'escola'     => $escola,
            'turma'      => $turma,
            'ocorrencias'=> $ocorrencias,
            'logoFile'   => "file://".$logoFile,
            'fotoFile'   => "file://".$fotoFile,
        ])->setPaper('a4');

        $content  = $pdf->output();
        $filename = "historico_ocorrencias_{$aluno->matricula}.pdf";

        return pdf_download($filename, $content);
    }

    // public function gerarPdf($alunoId)
    // {
    //     $aluno  = Aluno::findOrFail($alunoId);
    //     $escola = Escola::find(session('current_school_id'));

    //     $turma = optional(
    //         $aluno->enturmacao()->with('turma')->first()
    //     )->turma;

    //     // Caminhos locais
    //     $fotoAluno = public_path("storage/img-user/{$aluno->matricula}.png");
    //     if (!file_exists($fotoAluno))
    //         $fotoAluno = public_path("storage/img-user/padrao.png");

    //     $logoEscola = public_path("storage/{$escola->logo_path}");
    //     if (!file_exists($logoEscola))
    //         $logoEscola = public_path("storage/logos/padrao.png");

    //     // ✅ Usa função segura
    //     // $fotoAlunoBase64 = safe_image_base64($fotoAluno);
    //     // $logoBase64      = safe_image_base64($logoEscola);

    //     $fotoAlunoSvg = img_to_svg_base64($fotoAluno, 70, 70);
    //     $logoSvg = img_to_svg_base64($logoEscola, 60, 60);


    //     $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
    //         ->where('aluno_id', $aluno->id)
    //         ->orderByDesc('created_at')
    //         ->get();

    //     $pdf = Pdf::setOptions([
    //         'enable_remote' => true,
    //         'isRemoteEnabled' => true,
    //         'enable_php' => false,
    //     ])->loadView('professor.ocorrencias.pdf_historico', [
    //         'aluno' => $aluno,
    //         'escola' => $escola,
    //         'turma' => $turma,
    //         'ocorrencias' => $ocorrencias,
    //         'fotoAlunoSvg' => $fotoAlunoSvg,
    //         'logoSvg'      => $logoSvg,

    //     ]);

    //     // $pdf = Pdf::setOptions([
    //     //     'enable_remote' => true,
    //     //     'isRemoteEnabled' => true,
    //     //     'enable_php' => false,
    //     // ])->loadView('professor.ocorrencias.pdf_historico', [
    //     //     'aluno' => $aluno,
    //     //     'escola' => $escola,
    //     //     'turma' => $turma,
    //     //     'ocorrencias' => $ocorrencias,
    //     //     'fotoAlunoBase64' => $fotoAlunoBase64,
    //     //     'logoBase64' => $logoBase64,
    //     // ]);

    //     $content = $pdf->output();
    //     $filename = "historico_ocorrencias_{$aluno->matricula}.pdf";

    //     return pdf_download($filename, $content);
    // }

    // public function gerarPdf($alunoId)
    // {
    //     $aluno  = Aluno::findOrFail($alunoId);
    //     $escola = Escola::find(session('current_school_id'));

    //     // Turma atual via enturmacao → turma
    //     $turma = optional(
    //         $aluno->enturmacao()->with('turma')->first()
    //     )->turma;

    //     /* ========================================================
    //        ✅ FOTO DO ALUNO - BASE64
    //        ======================================================== */
    //     $fotoAlunoPath = public_path("storage/img-user/{$aluno->matricula}.png");
    //     if (!file_exists($fotoAlunoPath)) {
    //         $fotoAlunoPath = public_path("storage/img-user/padrao.png");
    //     }
    //     $fotoAlunoBase64 = img_to_base64($fotoAlunoPath);

    //     /* ========================================================
    //        ✅ LOGO DA ESCOLA - BASE64
    //        ======================================================== */
    //     if ($escola && $escola->logo_path) {
    //         $logoPath = public_path("storage/{$escola->logo_path}");
    //     } else {
    //         $logoPath = public_path("storage/logos/syrios.png");
    //     }
    //     $logoBase64 = img_to_base64($logoPath);

    //     /* ========================================================
    //        Ocorrências
    //        ======================================================== */
    //     $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
    //         ->where('aluno_id', $aluno->id)
    //         ->orderByDesc('created_at')
    //         ->get();

    //     /* ========================================================
    //        ✅ GERAÇÃO DO PDF
    //        ======================================================== */
    //     $pdf = Pdf::loadView('professor.ocorrencias.pdf_historico', [
    //         'escola'          => $escola,
    //         'aluno'           => $aluno,
    //         'turma'           => $turma,
    //         'ocorrencias'     => $ocorrencias,
    //         'fotoAlunoBase64' => $fotoAlunoBase64,
    //         'logoBase64'      => $logoBase64,
    //     ])->setPaper('a4');

    //     $content  = $pdf->output();
    //     $filename = "historico_ocorrencias_{$aluno->matricula}.pdf";

    //     return pdf_download($filename, $content);
    // }

    // public function gerarPdf($alunoId)
        // {
        //     $aluno  = Aluno::findOrFail($alunoId);
        //     $escola = Escola::find(session('current_school_id'));

        //     // Turma atual via enturmacao -> turma
        //     $turma = optional(
        //         $aluno->enturmacao()->with('turma')->first()
        //     )->turma;

        //     /*
        //     |--------------------------------------------------------------------------
        //     | ✅ FOTO DO ALUNO (100% compatível com DomPDF + Railway)
        //     |--------------------------------------------------------------------------
        //     | - usa URL pública (asset)
        //     | - evita caminho absoluto (public_path)
        //     | - se não existir, usa padrão
        //     */

        //     $fotoRel = "storage/img-user/{$aluno->matricula}.png";

        //     if (!file_exists(public_path($fotoRel))) {
        //         $fotoRel = "storage/img-user/padrao.png";
        //     }

        //     // ✅ URL completa acessível publicamente
        //     $fotoUrl = asset($fotoRel);

        //     /*
        //     |--------------------------------------------------------------------------
        //     | Ocorrências
        //     |--------------------------------------------------------------------------
        //     */
        //     $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
        //         ->where('aluno_id', $aluno->id)
        //         ->orderByDesc('created_at')
        //         ->get();

        //     /*
        //     |--------------------------------------------------------------------------
        //     | PDF usando URL e não caminho local
        //     |--------------------------------------------------------------------------
        //     */
        //     $pdf = Pdf::loadView('professor.ocorrencias.pdf_historico', [
        //         'escola'      => $escola,
        //         'aluno'       => $aluno,
        //         'turma'       => $turma,
        //         'ocorrencias' => $ocorrencias,
        //         'fotoFinal'   => $fotoUrl, // ✅ URL
        //     ])->setPaper('a4');

        //     /*
        //     |--------------------------------------------------------------------------
        //     | ✅ Download universal (Railway + Apache + Nginx)
        //     |--------------------------------------------------------------------------
        //     */
        //     $content   = $pdf->output();
        //     $filename  = "historico_ocorrencias_{$aluno->matricula}.pdf";

        //     return pdf_download($filename, $content);
    // }

    // public function gerarPdf($alunoId)
        // {
        //     $aluno  = Aluno::findOrFail($alunoId);
        //     $escola = Escola::find(session('current_school_id'));

        //     // Turma atual via enturmacao -> turma (primeira encontrada)
        //     $turma = optional(
        //         $aluno->enturmacao()->with('turma')->first()
        //     )->turma;

        //     // Caminho absoluto para foto
        //     $arquivoFoto  = 'storage/img-user/' . $aluno->matricula . '.png';
        //     $fotoAbsoluto = public_path($arquivoFoto);

        //     $fotoFinal = file_exists($fotoAbsoluto)
        //         ? $fotoAbsoluto
        //         : public_path('storage/img-user/padrao.png');

        //     $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
        //         ->where('aluno_id', $aluno->id)
        //         ->orderByDesc('created_at')
        //         ->get();

        //     // Gera PDF normalmente
        //     $pdf = Pdf::loadView('professor.ocorrencias.pdf_historico', [
        //         'escola'      => $escola,
        //         'aluno'       => $aluno,
        //         'turma'       => $turma,
        //         'ocorrencias' => $ocorrencias,
        //         'fotoFinal'   => $fotoFinal,
        //     ])->setPaper('a4');

        //     // ✅ Conteúdo bruto do PDF
        //     $content = $pdf->output();

        //     // ✅ Nome limpo
        //     $filename = 'historico_ocorrencias_'.$aluno->matricula.'.pdf';

        //     // ✅ Download universal (Railway + Apache + Nginx)
        //     return pdf_download($filename, $content);
    // }

    // public function gerarPdf($alunoId)
        // {
        //     $aluno  = Aluno::findOrFail($alunoId);
        //     $escola = Escola::find(session('current_school_id'));

        //     // Turma atual via enturmacao -> turma (primeira encontrada)
        //     $turma = optional(
        //         $aluno->enturmacao()->with('turma')->first()
        //     )->turma;

        //     // Para DomPDF, prefira caminho absoluto (public_path) ao invés de asset()
        //     $arquivoFoto = 'storage/img-user/' . $aluno->matricula . '.png';
        //     $fotoAbsoluto = public_path($arquivoFoto);
        //     $fotoFinal = file_exists($fotoAbsoluto)
        //         ? $fotoAbsoluto
        //         : public_path('storage/img-user/padrao.png');

        //     $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
        //         ->where('aluno_id', $aluno->id)
        //         ->orderByDesc('created_at')
        //         ->get();

        //     $pdf = Pdf::loadView('professor.ocorrencias.pdf_historico', [
        //         'escola'      => $escola,
        //         'aluno'       => $aluno,
        //         'turma'       => $turma,
        //         'ocorrencias' => $ocorrencias,
        //         'fotoFinal'   => $fotoFinal,
        //     ])->setPaper('a4');

        //     return $pdf->download('historico_ocorrencias_'.$aluno->matricula.'.pdf');
    // }

    public function historicoResumido($alunoId)
    {
        $schoolId = session('current_school_id');
        $aluno  = Aluno::findOrFail($alunoId);
        $escola = Escola::find($schoolId);

        // 🔍 Turma atual do aluno na escola logada
        $turma = optional(
            $aluno->enturmacao()
                ->where('school_id', $schoolId)
                ->with('turma')
                ->first()
        )->turma;

        // 🖼️ Foto do aluno (com fallback seguro)
        $fotoNome = $aluno->matricula . '.png';
        $fotoPath = public_path("storage/img-user/{$fotoNome}");
        $fotoFinal = file_exists($fotoPath)
            ? $fotoPath
            : public_path('storage/img-user/padrao.png');

        // 🧾 Ocorrências filtradas pela escola e turma atual
        $ocorrencias = Ocorrencia::with(['motivos', 'oferta.disciplina', 'professor.usuario'])
            ->where('aluno_id', $aluno->id)
            ->where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->get();

        return view('professor.ocorrencias.historico_resumido', compact(
            'aluno', 'turma', 'escola', 'fotoFinal', 'ocorrencias'
        ));
    }

    /**
     * Histórico completo em HTML.
     */
    public function historico($alunoId)
    {
        $schoolId = session('current_school_id');

        $aluno = Aluno::with(['enturmacao.turma'])
            ->where('id', $alunoId)
            ->firstOrFail();

        $turma = optional(
            $aluno->enturmacao()
                ->where('school_id', $schoolId)   // 🔒 restringe à escola logada
                ->with('turma')
                ->first()
        )->turma;

        $ocorrencias = Ocorrencia::with([
                'professor.usuario',
                'oferta.disciplina',
                'oferta.turma',
                'motivos'
            ])
            ->where('aluno_id', $alunoId)
            ->where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->get();

        return view('professor.ocorrencias.historico', compact('aluno', 'turma', 'ocorrencias'));
    }

    /**
     * Form de criação (para uma oferta e múltiplos alunos).
     */
    public function create(Request $request, $ofertaId)
    {
        $schoolId  = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $oferta = Oferta::with(['disciplina', 'turma'])->findOrFail($ofertaId);

        $alunosIds = $request->input('alunos', []);
        $alunos = Aluno::whereIn('id', $alunosIds)->get();

        $motivos = ModeloMotivo::daEscolaAtual()->orderBy('categoria')->get();

        return view('professor.ocorrencias.create', compact(
            'oferta', 'alunos', 'motivos', 'anoLetivo', 'schoolId'
        ));
    }

    /**
     * Grava uma ocorrência (para 1..N alunos), com motivos múltiplos.
     * ATENÇÃO: professor_id deve ser o id da tabela syrios_professor.
     */
    public function store(Request $request)
    {
        $schoolId  = session('current_school_id');
        $anoLetivo = session('ano_letivo_atual') ?? date('Y');

        $request->validate([
            'alunos'         => 'required|array|min:1',
            'oferta_id'      => 'nullable|integer|exists:' . prefix() . 'oferta,id',
            'motivos'        => 'nullable|array',
            'descricao'      => 'nullable|string',
            'local'          => 'nullable|string|max:100',
            'atitude'        => 'nullable|string|max:100',
            'outra_atitude'  => 'nullable|string|max:150',
            'comportamento'  => 'nullable|string|max:100',
            'sugestao'       => 'nullable|string|max:500',
        ]);

        // Professor da sessão (sempre usar o id da tabela syrios_professor!)
        $prof = auth()->user()->professor;
        if (!$prof) {
            return back()->with('error', 'Usuário logado não está vinculado como professor.');
        }

        try {
            DB::beginTransaction();

            foreach ($request->alunos as $alunoId) {
                $ocorrencia = Ocorrencia::create([
                    'school_id'       => $schoolId,
                    'ano_letivo'      => $anoLetivo,
                    'vigente'         => true,
                    'aluno_id'        => $alunoId,
                    'professor_id'    => $prof->id,             // ✅ professor.id (não user.id)
                    'oferta_id'       => $request->oferta_id,
                    'descricao'       => $request->descricao,
                    'local'           => $request->local,
                    'atitude'         => $request->atitude,
                    'outra_atitude'   => $request->outra_atitude,
                    'comportamento'   => $request->comportamento,
                    'sugestao'        => $request->sugestao,
                    'nivel_gravidade' => 1,
                ]);

                // Anexa motivos sem sobrescrever (pode repetir ao longo do tempo)
                if (!empty($request->motivos)) {
                    $ocorrencia->motivos()->syncWithoutDetaching($request->motivos);
                }

                Log::info('📘 Ocorrência registrada', [
                    'professor_id' => $prof->id,
                    'aluno_id'     => $alunoId,
                    'motivos'      => $request->motivos,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('professor.ofertas.index')
                ->with('success', '✅ Ocorrência registrada com sucesso.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Erro ao registrar ocorrência', [
                'erro'  => $e->getMessage(),
                'linha' => $e->getLine()
            ]);

            return back()->with('error', 'Ocorreu um erro ao registrar a ocorrência.');
        }
    }

    
    /**
     * Detalhes
     */
    public function show($id)
    {
        $ocorrencia = Ocorrencia::with([
                'aluno',
                'professor.usuario',
                'oferta.turma',
                'oferta.disciplina',
                'motivos'
            ])->findOrFail($id);

        $usuario     = auth()->user();
        $permissoes  = $this->podeGerenciar($ocorrencia, $usuario);

        return view('professor.ocorrencias.show', compact('ocorrencia', 'permissoes'));
    }

    /**
     * Atualiza status (arquivar/anular) — tipicamente feito por diretor de turma.
     */
    public function updateStatus($id, Request $request)
    {
        $ocorrencia = Ocorrencia::daEscolaAtual()->findOrFail($id);

        $novoStatus = (int) $request->input('status', 0); // 0=arquivada, 2=anulada
        $ocorrencia->update([
            'status'  => $novoStatus,
            'vigente' => false
        ]);

        return back()->with('success', 'Ocorrência atualizada com sucesso.');
    }

    /**
     * (Opcional) stubs para edit/update/destroy se ainda não tiver:
     */
    public function edit($id)
    {
        $ocorrencia = Ocorrencia::with(['aluno', 'oferta.turma', 'oferta.disciplina', 'motivos'])
            ->findOrFail($id);

        // Apenas autor pode editar:
        $per = $this->podeGerenciar($ocorrencia, auth()->user());
        if (!$per['autor']) {
            return back()->with('error', 'Você não tem permissão para editar esta ocorrência.');
        }

        $motivos = ModeloMotivo::daEscolaAtual()->orderBy('categoria')->get();

        return view('professor.ocorrencias.edit', compact('ocorrencia', 'motivos'));
    }

    public function update($id, Request $request)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);

        $per = $this->podeGerenciar($ocorrencia, auth()->user());
        if (!$per['autor']) {
            return back()->with('error', 'Você não tem permissão para editar esta ocorrência.');
        }

        $request->validate([
            'descricao'      => 'nullable|string',
            'local'          => 'nullable|string|max:100',
            'atitude'        => 'nullable|string|max:100',
            'outra_atitude'  => 'nullable|string|max:150',
            'comportamento'  => 'nullable|string|max:100',
            'sugestao'       => 'nullable|string|max:500',
            'motivos'        => 'nullable|array',
        ]);

        $ocorrencia->update($request->only([
            'descricao','local','atitude','outra_atitude','comportamento','sugestao'
        ]));

        // Atualiza motivos (sem apagar histórico anterior? aqui mantemos sem sobrescrever)
        if (!empty($request->motivos)) {
            $ocorrencia->motivos()->syncWithoutDetaching($request->motivos);
        }

        return redirect()->route('professor.ocorrencias.index')
            ->with('success', 'Ocorrência atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);

        $per = $this->podeGerenciar($ocorrencia, auth()->user());
        if (!$per['autor']) {
            return back()->with('error', 'Você não tem permissão para excluir esta ocorrência.');
        }

        $ocorrencia->delete();

        return redirect()->route('professor.ocorrencias.index')
            ->with('success', 'Ocorrência excluída com sucesso.');
    }
}

