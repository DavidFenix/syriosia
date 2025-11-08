<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagController;

Route::get('/', function () {
    return view('welcome');
});



//diagnóstivo fora do middleware mostrou que a sessão funciona
Route::get('/diagini', [DiagController::class, 'indexini']);
Route::get('/diagini/cookie-testini', [DiagController::class, 'cookieTestini']);

Route::middleware(['web'])->group(function () {

    // Página inicial
    //Route::get('/', fn() => redirect()->route('login'));

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
