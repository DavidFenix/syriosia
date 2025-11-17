<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Cookie;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DiagController;
use App\Http\Controllers\DiagPdfController;

// Master
use App\Http\Controllers\Master\EscolaController as MasterEscolaController;
use App\Http\Controllers\Master\RoleController as MasterRoleController;
use App\Http\Controllers\Master\UsuarioController as MasterUsuarioController;
use App\Http\Controllers\Master\DashboardController as MasterDashboardController;
use App\Http\Controllers\Master\ImagemController;

// Secretaria
use App\Http\Controllers\Secretaria\EscolaController as SecretariaEscolaController;
use App\Http\Controllers\Secretaria\UsuarioController as SecretariaUsuarioController;

// Escola
use App\Http\Controllers\Escola\AlunoController;
use App\Http\Controllers\Escola\DashboardController;
use App\Http\Controllers\Escola\DisciplinaController;
use App\Http\Controllers\Escola\ProfessorController;
use App\Http\Controllers\Escola\TurmaController;
use App\Http\Controllers\Escola\UsuarioController as EscolaUsuarioController;
use App\Http\Controllers\Escola\RegimentoController;
use App\Http\Controllers\Escola\ModeloMotivoController;
use App\Http\Controllers\Escola\AlunoFotoController;
use App\Http\Controllers\Escola\AlunoFotoLoteController;
use App\Http\Controllers\Escola\EnturmacaoController;
use App\Http\Controllers\Escola\LotacaoController;
use App\Http\Controllers\Escola\DiretorTurmaController;
use App\Http\Controllers\Escola\IdentidadeController;

// Professor
use App\Http\Controllers\Professor\{
    DashboardController as ProfessorDashboardController,
    OfertaController,
    OcorrenciaController,
    RelatorioController,
    PerfilController
};

use App\Http\Controllers\Escola\CadastroLoteProfessorController;
use App\Http\Controllers\Escola\CadastroLoteAlunoController;
use App\Http\Controllers\Escola\CadastroLoteDisciplinaController;
use App\Http\Controllers\Escola\CadastroLoteOfertaController;

//rotas de testes
    Route::get('/', function () {
        return view('welcome');
    });

    //somente para testes, remova em produção
    Route::get('/kill-cookie', function () {
        return response('Cookie removido')
            ->cookie('syriosia_session', null, -1, '/', 'syriosia.up.railway.app', true, true, false, 'None')
            ->cookie('XSRF-TOKEN', null, -1, '/', 'syriosia.up.railway.app', true, true, false, 'None');
    });

    //diagnóstivo fora do middleware mostrou que a sessão funciona
    Route::get('/diagini', [DiagController::class, 'indexini']);
    Route::get('/diagini/cookie-testini', [DiagController::class, 'cookieTestini']);

    Route::middleware(['web'])->group(function () {

        // Página inicial
        Route::get('/', fn() => redirect()->route('login'));

        // Login / Logout (públicas)
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        // Regimento público
        Route::get('regimento/{school}', [RegimentoController::class, 'visualizar'])
            ->name('regimento.visualizar');

        // Login temporário (placeholder)
        //Route::get('/login', fn() => 'Tela de login')->name('login');

        // ==================================================
        // DIAGNÓSTICO
        //1º acesse /diag/cookie-test para carregar o cookie
        //2º acesse /diag para ver se o cookie foi recebido, ou inspecione o codigo no navegador para ver o cookie
        // ==================================================
        Route::prefix('diag')->group(function () { 

            Route::get('/', [DiagController::class, 'index'])->name('diag.index');

            // se ainda não criou os métodos headers/cookies/etc, comente estes:
            // Route::get('/headers', [DiagController::class, 'headers'])->name('diag.headers'); 
            // Route::get('/cookies', [DiagController::class, 'cookies'])->name('diag.cookies'); 
            // Route::get('/set-cookie', [DiagController::class, 'setCookie'])->name('diag.setcookie'); 
            // Route::get('/configs', [DiagController::class, 'configs'])->name('diag.configs'); 
            
            Route::get('/cookie-test', function () { 
                return response('ok')->cookie('probe', '1', 0, null, null, true, true, false, 'None'); 
            });

            Route::get('/storage', [DiagController::class, 'storage'])->name('diag.storage');

            Route::get('/pdf-diag', [DiagController::class, 'pdfDiag'])->name('diag.pdf.diag');
            Route::get('/pdf-download', [DiagController::class, 'pdfDownloadTest'])->name('diag.pdf.download');
            Route::get('/raw-pdf', function () {
                $content = "%PDF-1.4\nHello world\n%%EOF";
                return response($content, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Length', strlen($content));
            });
            Route::get('/raw-binary', function () {
                $binary = random_bytes(1024);
                return response($binary)
                    ->header('Content-Type', 'application/octet-stream')
                    ->header('Content-Length', strlen($binary));
            });
            Route::get('/raw-fixed', function () {
                $content = "%PDF-1.4\nHello railway\n%%EOF";
                // DESATIVA COMPACTAÇÃO E BUFFER EM QUALQUER SERVIDOR
                ini_set('zlib.output_compression', '0');
                ini_set('output_buffering', 'off');
                return response()->streamDownload(
                    function () use ($content) {
                        echo $content;
                        flush();  // força saída imediata
                    },
                    'raw-fixed.pdf',
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Length' => strlen($content),
                        'Content-Encoding' => 'none',
                        'Cache-Control' => 'private, no-transform',
                        'Transfer-Encoding' => 'identity'
                    ]
                );
            });
            Route::get('/pdf-full', [DiagPdfController::class, 'full']);

            Route::get('/diag-gd', function () {
                $status = extension_loaded('gd');
                $details = $status ? gd_info() : null;

                return response()->json([
                    'GD_Ativo' => $status ? '✅ Sim' : '❌ Não',
                    'Versão' => $details['GD Version'] ?? null,
                    'Suporte JPEG' => $details['JPEG Support'] ?? null,
                    'Suporte PNG' => $details['PNG Support'] ?? null,
                    'Suporte WebP' => $details['WebP Support'] ?? null,
                ]);
            });







        }); 

        // ==================================================
        // Cache Clear
        // ==================================================
        Route::get('/cache-clear', function () {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            return "Cache limpo!";
        });

        // ==================================================
        // Bloco WAY (login fake)
        // ==================================================
        Route::get('/way', function () {
            return '
            <h2>Login Teste</h2>
            <form method="post" action="/waylogin">
              <input type="email" name="email" placeholder="Email" required><br><br>
              <input type="password" name="password" placeholder="Senha" required><br><br>
              <button type="submit">Entrar</button>
            </form>
            <hr><a href="/waydiag">Diagnóstico</a>';
        });

        Route::post('/waylogin', function (Request $request) {
            Session::put('user', [
                'email' => $request->email,
                'logged_at' => now()->toDateTimeString()
            ]);
            return redirect('/waydashboard');
        });

        Route::get('/waydashboard', function () {
            if (!Session::has('user')) {
                return redirect('/way');
            }
            $u = Session::get('user');
            return "
            <h2>Área Protegida</h2>
            <p>Email: <b>{$u['email']}</b></p>
            <p>Login em: {$u['logged_at']}</p>
            <a href='/waylogout'>Sair</a> | <a href='/waydiag'>Diagnóstico</a>";
        });

        Route::get('/waylogout', function () {
            Session::flush();
            return redirect('/way');
        });

        Route::get('/waydiag', function (Request $r) {
            $headers = [];
            foreach ($r->headers->all() as $k => $v) {
                $headers[$k] = implode('; ', $v);
            }

            return response()->make("
            <h2>Diagnóstico</h2>
            <p>HTTPS detectado: " . ($r->isSecure() ? 'Sim' : 'Não') . "</p>
            <h3>Cookies</h3><pre>" . print_r($r->cookies->all(), true) . "</pre>
            <h3>Sessão</h3><pre>" . print_r(session()->all(), true) . "</pre>
            <h3>Headers</h3><pre>" . print_r($headers, true) . "</pre>
            <a href='/way'>Voltar</a>", 200, ['Content-Type' => 'text/html']);
        });

    });



/*
|--------------------------------------------------------------------------
| Pós-Login (com sessão carregada) — Escolha de Contexto
|--------------------------------------------------------------------------
| Essas rotas precisam apenas do usuário autenticado, sem exigir contexto.
| Aqui o cookie de sessão já foi entregue ao navegador.
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/choose-school', [LoginController::class, 'chooseSchool'])->name('choose.school');
    Route::get('/choose-role/{schoolId}', [LoginController::class, 'chooseRole'])->name('choose.role');
    Route::post('/set-context', [LoginController::class, 'setContextPost'])->name('set.context');
});


/*
|--------------------------------------------------------------------------
| Rotas Protegidas por Contexto (auth + ensure.context)
|--------------------------------------------------------------------------
| A partir daqui, o contexto (current_school_id/current_role) já deve existir.
| Evitamos rodar ensure.context antes do cookie ser entregue (problema original).
*/
Route::middleware(['auth', 'ensure.context'])->group(function () {

    //dashboard global que direciona corretamente baseando-se na current_role da sessão
    Route::get('/dashboard', function () {

        // Se existir flash ANTES de entrar nessa rota, vamos preservar
        if (session()->has('error')) session()->keep('error');
        if (session()->has('success')) session()->keep('success');

        $role = session('current_role');

        return match ($role) {
            'master'     => redirect()->route('master.dashboard'),
            'secretaria' => redirect()->route('secretaria.dashboard'),
            'escola'     => redirect()->route('escola.dashboard'),
            'professor'  => redirect()->route('professor.dashboard'),

            default      => redirect('/')
                ->with('error', 'Nenhum contexto encontrado. Selecione a escola e papel.')
        };

    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Rotas do Master
    |--------------------------------------------------------------------------
    */
    Route::prefix('master')
        ->middleware(['ensure.role.master'])
        ->name('master.')
        ->group(function () {
            Route::get('dashboard', [MasterDashboardController::class, 'index'])->name('dashboard');
            Route::get('/', fn () => redirect()->route('master.dashboard'));

            Route::resource('escolas', MasterEscolaController::class)->except(['show']);
            Route::get('escolas/{escola}/detalhes', [MasterEscolaController::class, 'detalhes'])
                ->name('escolas.detalhes');

            Route::resource('roles', MasterRoleController::class)->only(['index']);
            Route::resource('usuarios', MasterUsuarioController::class);

            // Associações Escola Mãe ↔ Escola Filha
            Route::get('associacoes', [MasterEscolaController::class, 'associacoes'])->name('escolas.associacoes');
            Route::post('associacoes', [MasterEscolaController::class, 'associarFilha'])->name('escolas.associar');

            Route::post('usuarios/{usuario}/vincular', [MasterUsuarioController::class, 'vincular'])
                ->name('usuarios.vincular');

            // Gestão de roles específicas por usuario
            Route::get('usuarios/{usuario}/roles', [MasterUsuarioController::class, 'editRoles'])
                ->name('usuarios.roles.edit');
            Route::post('usuarios/{usuario}/roles', [MasterUsuarioController::class, 'updateRoles'])
                ->name('usuarios.roles.update');

            // Confirmação/Exclusão
            Route::get('usuarios/{usuario}/confirm-destroy', [MasterUsuarioController::class, 'confirmDestroy'])
                ->name('usuarios.confirmDestroy');
            Route::delete('usuarios/{usuario}', [MasterUsuarioController::class, 'destroy'])
                ->name('usuarios.destroy');

            // Imagens
            Route::get('imagens', [ImagemController::class, 'index'])->name('imagens.index');
            Route::post('imagens/limpar', [ImagemController::class, 'limpar'])->name('imagens.limpar');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas da Secretaria
    |--------------------------------------------------------------------------
    */
    Route::prefix('secretaria')
        ->middleware(['ensure.role.secretaria'])
        ->name('secretaria.')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('secretaria.escolas.index'))->name('dashboard');

            Route::resource('escolas', SecretariaEscolaController::class)->except(['show']);
            Route::resource('usuarios', SecretariaUsuarioController::class)->except(['show']);

            Route::post('usuarios/{usuario}/vincular', [SecretariaUsuarioController::class, 'vincular'])
                ->name('usuarios.vincular');

            Route::get('usuarios/{usuario}/roles', [SecretariaUsuarioController::class, 'editRoles'])
                ->name('usuarios.roles.edit');
            Route::post('usuarios/{usuario}/roles', [SecretariaUsuarioController::class, 'updateRoles'])
                ->name('usuarios.roles.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas da Escola
    |--------------------------------------------------------------------------
    */
    Route::prefix('escola')
        ->middleware(['ensure.role.escola'])
        ->name('escola.')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('escola.dashboard'));
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Usuários (professores, pais, etc.)
            Route::resource('usuarios', EscolaUsuarioController::class)->except(['show']);
            Route::post('usuarios/{usuario}/vincular', [EscolaUsuarioController::class, 'vincular'])->name('usuarios.vincular');

            // Professores
            Route::resource('professores', ProfessorController::class)->except(['show']);

            // Disciplinas
            Route::resource('disciplinas', DisciplinaController::class)->except(['show']);

            // Turmas
            Route::resource('turmas', TurmaController::class)->except(['show']);

            // Alunos
            Route::resource('alunos', AlunoController::class)->except(['show']);

            // Roles por usuário da Escola
            Route::get('usuarios/{usuario}/roles', [EscolaUsuarioController::class, 'editRoles'])
                ->name('usuarios.roles.edit');
            Route::post('usuarios/{usuario}/roles', [EscolaUsuarioController::class, 'updateRoles'])
                ->name('usuarios.roles.update');

            // Vincular aluno existente à escola atual
            Route::post('alunos/{aluno}/vincular', [AlunoController::class, 'vincular'])
                ->name('alunos.vincular');

            // Enturmações (vínculos aluno–turma)
            Route::resource('enturmacao', EnturmacaoController::class)->except(['show']);
            Route::post('enturmacao/storeBatch', [EnturmacaoController::class, 'storeBatch'])
                ->name('enturmacao.storeBatch');

            // Lotação
            Route::resource('lotacao', LotacaoController::class)->except(['show']);
            Route::prefix('lotacao')->name('lotacao.')->group(function () {
                Route::get('diretor_turma', [DiretorTurmaController::class, 'index'])
                    ->name('diretor_turma.index');
                Route::post('diretor_turma/update', [DiretorTurmaController::class, 'update'])
                    ->name('diretor_turma.update');
                Route::delete('diretor_turma/{id}', [DiretorTurmaController::class, 'destroy'])
                    ->name('diretor_turma.destroy');
            });

            // Identidade visual
            Route::get('identidade', [IdentidadeController::class, 'edit'])
                ->name('identidade.edit');
            Route::post('identidade', [IdentidadeController::class, 'update'])
                ->name('identidade.update');

            // Regimento (painel da escola)
            Route::get('regimento', [RegimentoController::class, 'index'])->name('regimento.index');
            Route::post('regimento', [RegimentoController::class, 'update'])->name('regimento.update');

            // Motivos de Ocorrência
            Route::resource('motivos', ModeloMotivoController::class)->except(['show']);
            // Importar motivos de outras escolas
            Route::get('motivos/importar', [ModeloMotivoController::class, 'importar'])
                ->name('motivos.importar');
            Route::post('motivos/importar', [ModeloMotivoController::class, 'importarSalvar'])
                ->name('motivos.importar.salvar');

            // Uploads de fotos
            Route::get('alunos/{aluno}/foto', [AlunoFotoController::class, 'edit'])->name('alunos.foto.edit');
            Route::post('alunos/{aluno}/foto', [AlunoFotoController::class, 'update'])->name('alunos.foto.update');

            Route::get('alunos/fotos-lote', [AlunoFotoLoteController::class, 'index'])->name('alunos.fotos.lote');
            Route::post('alunos/fotos-lote', [AlunoFotoLoteController::class, 'store'])->name('alunos.fotos.lote.store');

            // cadastro em lote de professores (duas etapas)
            Route::prefix('professores-lote')->group(function () {

                Route::get('/', [CadastroLoteProfessorController::class, 'index'])
                    ->name('professores.lote.index');

                Route::get('/modelo', [CadastroLoteProfessorController::class, 'modelo'])
                    ->name('professores.lote.modelo');

                Route::post('/preview', [CadastroLoteProfessorController::class, 'preview'])
                    ->name('professores.lote.preview');

                Route::post('/importar', [CadastroLoteProfessorController::class, 'importar'])
                    ->name('professores.lote.importar');
            });

            // cadastro em lote de alunos (duas etapas)
            Route::prefix('alunos-lote')->group(function () {

                Route::get('/', [CadastroLoteAlunoController::class, 'index'])
                    ->name('alunos.lote.index');

                Route::get('/modelo', [CadastroLoteAlunoController::class, 'modelo'])
                    ->name('alunos.lote.modelo');

                Route::post('/preview', [CadastroLoteAlunoController::class, 'preview'])
                    ->name('alunos.lote.preview');

                Route::post('/importar', [CadastroLoteAlunoController::class, 'importar'])
                    ->name('alunos.lote.importar');
            });

            Route::prefix('disciplinas-lote')->group(function () {

                Route::get('/', [CadastroLoteDisciplinaController::class, 'index'])
                    ->name('disciplinas.lote.index');

                Route::get('/modelo', [CadastroLoteDisciplinaController::class, 'modelo'])
                    ->name('disciplinas.lote.modelo');

                Route::post('/preview', [CadastroLoteDisciplinaController::class, 'preview'])
                    ->name('disciplinas.lote.preview');

                Route::post('/importar', [CadastroLoteDisciplinaController::class, 'importar'])
                    ->name('disciplinas.lote.importar');
            });

            Route::prefix('ofertas-lote')->group(function () {

                Route::get('/', [CadastroLoteOfertaController::class, 'index'])
                    ->name('ofertas.lote.index');

                Route::get('/modelo', [CadastroLoteOfertaController::class, 'modelo'])
                    ->name('ofertas.lote.modelo');

                Route::post('/preview', [CadastroLoteOfertaController::class, 'preview'])
                    ->name('ofertas.lote.preview');

                Route::post('/importar', [CadastroLoteOfertaController::class, 'importar'])
                    ->name('ofertas.lote.importar');
            });





    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Professor
    |--------------------------------------------------------------------------
    */
    Route::prefix('professor')
        ->middleware(['ensure.role.professor'])
        ->name('professor.')
        ->group(function () {

            // Painel e perfil
            Route::get('dashboard', [ProfessorDashboardController::class, 'index'])
                ->name('dashboard');

            // Ofertas
            Route::prefix('ofertas')->name('ofertas.')->group(function () {
                Route::get('/', [OfertaController::class, 'index'])->name('index');
                Route::get('{oferta}/alunos', [OfertaController::class, 'alunos'])->name('alunos');
                Route::post('{oferta}/alunos', [OfertaController::class, 'alunosPost'])->name('alunos.post');

                // Ocorrências por oferta
                Route::get('{oferta}/ocorrencias/create', [OcorrenciaController::class, 'create'])
                    ->name('ocorrencias.create');
                Route::post('ocorrencias/store', [OcorrenciaController::class, 'store'])
                    ->name('ocorrencias.store');
            });

            // Ocorrências (rotas gerais)
            Route::prefix('ocorrencias')->name('ocorrencias.')->group(function () {
                Route::get('/', [OcorrenciaController::class, 'index'])->name('index');
                Route::get('{id}', [OcorrenciaController::class, 'show'])->name('show');
                Route::get('{id}/edit', [OcorrenciaController::class, 'edit'])->name('edit');
                Route::put('{id}', [OcorrenciaController::class, 'update'])->name('update');
                Route::delete('{id}', [OcorrenciaController::class, 'destroy'])->name('destroy');
                Route::patch('{id}/status', [OcorrenciaController::class, 'updateStatus'])->name('updateStatus');

                // Encaminhar / arquivar (somente diretor)
                Route::get('{id}/encaminhar', [OcorrenciaController::class, 'encaminhar'])
                    ->name('encaminhar');
                Route::post('{id}/encaminhar', [OcorrenciaController::class, 'salvarEncaminhamento'])
                    ->name('encaminhar.salvar');

                // Histórico do aluno
                Route::get('historico/{aluno}', [OcorrenciaController::class, 'historico'])
                    ->name('historico');

                // Histórico resumido (visual e PDF)
                Route::get('historico-resumido/{aluno}', [OcorrenciaController::class, 'historicoResumido'])
                    ->name('historico_resumido');
                Route::get('pdf/{aluno}', [OcorrenciaController::class, 'gerarPdf'])
                    ->name('pdf');
            });

            // Rota pública sob /professor (mantida como no original)
            Route::get('regimento/{school}', [RegimentoController::class, 'visualizar'])
                ->name('regimento.visualizar');

            // Relatórios
            Route::get('relatorios', [RelatorioController::class, 'index'])
                ->name('relatorios.index');
    });

});