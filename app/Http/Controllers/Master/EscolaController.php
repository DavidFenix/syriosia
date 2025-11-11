<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscolaController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->get('tipo'); // 'mae', 'filha', ou null
        
        $query  = Escola::query();

        if ($filtro === 'mae') {
            $query->whereNull('secretaria_id');
        } elseif ($filtro === 'filha') {
            $query->whereNotNull('secretaria_id');
        }

        //$escolas = $query->with('mae')->orderBy('nome_e')->get();
        
        $escolas = Escola::with('mae')->filtrar($filtro)->get();
        
        $maes    = Escola::whereNull('secretaria_id')->orderBy('nome_e')->get();

        return view('master.escolas.index', compact('escolas', 'maes', 'filtro'));
    }

    public function detalhes(Escola $escola)
    {
        // Busca dados completos da escola
        $escola->load(['mae', 'filhas', 'usuarios.roles']);

        // Tipo textual
        $tipo = $escola->is_master
            ? 'Secretaria Master'
            : ($escola->filhas->count() > 0
                ? 'Escola Mãe'
                : ($escola->mae ? 'Escola Filha' : 'Escola Isolada'));

        return view('master.escolas.detalhes', compact('escola', 'tipo'));
    }

    public function create()
    {
        $maes = Escola::whereNull('secretaria_id')->orderBy('nome_e')->get();
        return view('master.escolas.create', compact('maes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome_e'       => 'required|string|max:150',
            'inep'         => 'nullable|string|max:20',
            'cnpj'         => 'nullable|string|max:20',
            'cidade'       => 'nullable|string|max:100',
            'estado'       => 'nullable|string|max:100',
            'endereco'     => 'nullable|string|max:255',
            'telefone'     => 'nullable|string|max:20',
            'secretaria_id'=> 'nullable|integer|exists:syrios_escola,id',
        ]);

        Escola::create($data);
        return redirect()->route('master.escolas.index')
            ->with('success', 'Instituição criada!');
    }

    public function edit(Escola $escola)
    {
        $auth = auth()->user();

        // 🔒 Regra 1: regra:apenas Super Master pode editar a escola master
        if ($escola->is_master && !$auth->is_super_master) {
            return redirect()
                ->route('master.escolas.index')
                ->with('error', 'Apenas o Super Master pode editar a escola principal.');
        }

        // 🔒 Regra 2: regra:se for uma secretaria, o select de mãe não deve ser exibido
        // (a view já faz isso, mas filtramos aqui também)
        $maes = Escola::whereNull('secretaria_id')
            ->where('id', '<>', $escola->id)
            ->orderBy('nome_e')
            ->get();

        return view('master.escolas.edit', compact('escola', 'maes'));
    }

    public function update(Request $request, Escola $escola)
    {
        $auth = auth()->user();

        // 🔒 1) Proteção: regra:somente Super Master pode alterar escola master
        if ($escola->is_master && !$auth->is_super_master) {
            return redirect()
                ->route('master.escolas.index')
                ->with('error', 'Apenas o Super Master pode atualizar a escola principal.');
        }

        // 🔹 Validação básica
        $data = $request->validate([
            'nome_e'        => 'required|string|max:150',
            'inep'          => 'nullable|string|max:20',
            'cnpj'          => 'nullable|string|max:20',
            'cidade'        => 'nullable|string|max:100',
            'estado'        => 'nullable|string|max:100',
            'endereco'      => 'nullable|string|max:255',
            'telefone'      => 'nullable|string|max:20',
            'secretaria_id' => 'nullable|integer|exists:syrios_escola,id',
        ]);

        // 🔒 2) regra:Uma escola não pode ser sua própria secretaria
        if (isset($data['secretaria_id']) && (int)$data['secretaria_id'] === (int)$escola->id) {
            return back()->withErrors(['secretaria_id' => 'Uma escola não pode ser sua própria secretaria.'])
                         ->withInput();
        }

        // 🔒 3) regra:Se for uma secretaria (mãe), ela não pode virar filha
        if ($escola->secretaria_id === null && isset($data['secretaria_id']) && $data['secretaria_id'] !== null) {
            return back()->withErrors(['secretaria_id' => 'Uma secretaria não pode ser vinculada a outra.'])
                         ->withInput();
        }

        // 🔒 4) regra:Se for uma escola (filha), ela não pode deixar de ser filha
        if ($escola->secretaria_id !== null && empty($data['secretaria_id'])) {
            return back()->withErrors(['secretaria_id' => 'Uma escola não pode deixar de ter secretaria.'])
                         ->withInput();
        }

        // 🔒 5) regra:Se o usuário não for Master, ele não pode trocar de mãe
        $isMaster = $auth->is_super_master || $auth->hasRole('master');
        if (!$isMaster && isset($data['secretaria_id']) && $data['secretaria_id'] != $escola->secretaria_id) {
            return back()->withErrors(['secretaria_id' => 'Apenas usuários Master podem alterar a secretaria vinculada.'])
                         ->withInput();
        }

        // ✅ Tudo certo, atualiza
        $escola->update($data);

        return redirect()->route('master.escolas.index')
            ->with('success', 'Instituição atualizada com sucesso!');
    }

    public function destroy(Escola $escola)
    {
        $auth = auth()->user();

        // 🔒 Impede excluir a escola master (qualquer usuário)
        if ($escola->is_master) {
            return redirect()
                ->route('master.escolas.index')
                ->with('error', 'A escola principal não pode ser excluída.');
        }
        
        // regra:DELETE SEGURO; evita quebrar FKs
        $deps = [
            'usuarios'      => DB::table('syrios_usuario')->where('school_id', $escola->id)->count(),
            'professores'   => DB::table('syrios_professor')->where('school_id', $escola->id)->count(),
            'alunos'        => DB::table('syrios_aluno')->where('school_id', $escola->id)->count(),
            'turmas'        => DB::table('syrios_turma')->where('school_id', $escola->id)->count(),
            'disciplinas'   => DB::table('syrios_disciplina')->where('school_id', $escola->id)->count(),
            'ofertas'       => DB::table('syrios_oferta')->where('school_id', $escola->id)->count(),
            'modelo_motivo'     => DB::table('syrios_modelo_motivo')->where('school_id', $escola->id)->count(),
            'enturmacao'    => DB::table('syrios_enturmacao')->where('school_id', $escola->id)->count(),
            'notificacao'   => DB::table('syrios_notificacao')->where('school_id', $escola->id)->count(),
            'sessao'        => DB::table('syrios_sessao')->where('school_id', $escola->id)->count(),
            'visao_aluno'   => DB::table('syrios_visao_aluno')->where('school_id', $escola->id)->count(),
            'filhas'        => DB::table('syrios_escola')->where('secretaria_id', $escola->id)->count(),
        ];

        $bloqs = array_filter($deps, function ($c) { return $c > 0; });

        //regra:não excluir escola quando possui vínculos
        if (!empty($bloqs)) {
            $lista = [];
            foreach ($bloqs as $tabela => $qtd) {
                $lista[] = "$tabela: $qtd";
            }
            return redirect()->route('master.escolas.index')
                ->with('error', 'Não é possível excluir. Existem vínculos → '.implode(', ', $lista));
        }

        // 🔒 regra:Impede excluir a escola master
        if ($escola->is_master) {
            return redirect()
                ->route('master.escolas.index')
                ->with('error', 'A escola master não pode ser excluída.');
        }

        $escola->delete();

        return redirect()
            ->route('master.escolas.index')
            ->with('success', 'Escola excluída!');

    }

    /*
    ✅ Resumo do comportamento final
    Ação     Super Master    Master comum    Secretaria / Escola
    Ver lista de associações            ✅   ✅   ✅
    Associar escola à secretaria Master ✅   🚫   🚫
    Associar escolas filhas normais     ✅   ✅   🚫
    Ver secretarias Master no select    ✅   🚫   🚫
    */
    public function associarFilha(Request $request)
    {
        $auth = auth()->user();

        // 🔒 Regra 1: apenas Master pode fazer associações
        if (!$auth->hasRole('master') && !$auth->is_super_master) {
            return redirect()->route('master.escolas.associacoes')
                             ->with('error', 'Somente usuários Master podem criar associações entre escolas.');
        }

        $request->validate([
            'mae_id' => 'required|exists:syrios_escola,id',
            'filha_id' => 'required|exists:syrios_escola,id',
        ]);

        $mae = Escola::findOrFail($request->mae_id);
        $filha = Escola::findOrFail($request->filha_id);

        // 🔒 Regra 2: apenas Super Master pode associar escolas à secretaria Master
        if ($mae->is_master && !$auth->is_super_master) {
            return redirect()->route('master.escolas.associacoes')
                             ->with('error', 'Apenas o Super Master pode associar escolas à secretaria principal.');
        }

        // 🔒 Impede loop ou autoassociação
        if ($mae->id === $filha->id) {
            return redirect()->route('master.escolas.associacoes')
                             ->with('error', 'Uma escola não pode ser sua própria mãe.');
        }

        // 🔒 Impede associar secretaria (mãe) como filha
        if ($filha->is_master) {
            return redirect()->route('master.escolas.associacoes')
                             ->with('error', 'Uma secretaria principal não pode ser filha de outra escola.');
        }

        // ✅ Aplica associação
        $filha->secretaria_id = $mae->id;
        $filha->save();

        return redirect()->route('master.escolas.associacoes')
                         ->with('success', 'Escola filha associada com sucesso!');
    }

    public function associacoes()
    {
        $auth = auth()->user();

        // 🔍 Escolas mãe (secretarias)
        $escolasMaeQuery = Escola::whereNull('secretaria_id');

        // 🔒 Oculta a secretaria master se não for super master
        if (!$auth->is_super_master) {
            $escolasMaeQuery->where('is_master', 0);
        }

        $escolasMae = $escolasMaeQuery->orderBy('nome_e')->get();

        // 🔍 Identifica IDs de escolas que são mães (têm filhas)
        $idsQueSaoMae = Escola::whereNotNull('secretaria_id')
            ->pluck('secretaria_id')
            ->unique()
            ->toArray();

        // ✅ Escolas disponíveis como filhas:
        // - não são secretarias principais (is_master = 0)
        // - não são mães de ninguém (não aparecem como secretaria_id)
        $escolasFilhasDisponiveis = Escola::where('is_master', 0)
            ->whereNotIn('id', $idsQueSaoMae)
            ->orderBy('nome_e')
            ->get();

        // 🔎 Mãe selecionada (para exibir suas filhas)
        $maeSelecionada = request('mae_id');
        $escolasFilhas = collect();
        $nomeMae = null;

        if ($maeSelecionada) {
            $mae = Escola::find($maeSelecionada);
            if ($mae) {
                $nomeMae = $mae->nome_e;
                $escolasFilhas = $mae->filhas;
            }
        }

        return view('master.escolas.associacoes', compact(
            'escolasMae',
            'maeSelecionada',
            'escolasFilhas',
            'escolasFilhasDisponiveis',
            'nomeMae'
        ));
    }

    //passo 2: esta função foi chamada pela rota ../master/escolas-associacoes2
    //ao terminar vai retornar compact(dados) para a view /master/escolas/associacoes2.blade.php
    public function associacoes2()
    {
        // escolas mãe = secretaria_id NULL
        $escolasMae = Escola::whereNull('secretaria_id')->get();

        // pega o ID da mãe selecionada (se houver na URL ?mae_id=)
        $maeSelecionada = request('mae_id');

        $escolasFilhas = collect();
        $nomeMae = null;

        if ($maeSelecionada) {
            $mae = Escola::find($maeSelecionada);
            if ($mae) {
                $nomeMae = $mae->nome_e;
                $escolasFilhas = $mae->filhas; // usa o relacionamento
            }
        }

        //os resultados em compact vai para a view master/escolas/associacoes2.php
        return view('master.escolas.associacoes2', compact('escolasMae', 'maeSelecionada', 'escolasFilhas', 'nomeMae'));
    }


}
