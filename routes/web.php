<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Cookie;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DiagController;

use App\Http\Controllers\Escola\ProfessorController;

// Professor
use App\Http\Controllers\Professor\{
    DashboardController as ProfessorDashboardController,
    OfertaController,
    OcorrenciaController,
    RelatorioController,
    PerfilController
};

Route::get('/', function () {
    return view('welcome');
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

    /*
    |--------------------------------------------------------------------------
    | Rotas do Professor
    |--------------------------------------------------------------------------
    */
    Route::prefix('professor')
        ->middleware(['role:professor'])
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


        });

});