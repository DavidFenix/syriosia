<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class DiagController extends Controller
{
    public function index(Request $request)
    {
        // -----------------------------------------------------
        // 1. STATUS DO AMBIENTE
        // -----------------------------------------------------
        $isHttps = $request->isSecure() || $request->header('x-forwarded-proto') === 'https';
        $appEnv = env('APP_ENV', 'unknown');
        $appUrl = env('APP_URL', '');
        $cookieTest = isset($_COOKIE['probe']);
        $secureCookie = env('SESSION_SECURE_COOKIE');

        $status = [
            'railway' => $appUrl && str_contains($appUrl, 'railway.app'),
            'https' => $isHttps,
            'cookie_received' => $cookieTest,
            'secure_cookie' => $secureCookie,
            'env' => $appEnv,
        ];

        // -----------------------------------------------------
        // 2. VARIÁVEIS DE AMBIENTE (mascaradas)
        // -----------------------------------------------------
        $rawEnv = $_ENV + getenv();
        ksort($rawEnv);

        $sensitive = [
            'APP_KEY', 'DB_PASSWORD', 'DB_USERNAME', 'DB_HOST', 'DB_DATABASE',
            'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY',
            'MAIL_PASSWORD', 'PUSHER_APP_SECRET', 'REDIS_PASSWORD',
        ];

        $envMasked = collect($rawEnv)->mapWithKeys(function ($v, $k) use ($sensitive) {
            $hide = collect($sensitive)->contains(fn($item) => str_contains($k, $item));
            return [$k => $hide ? '**********' : $v];
        });

        // -----------------------------------------------------
        // 3. CONFIG CORS
        // -----------------------------------------------------
        $corsConfig = Config::get('cors');

        // -----------------------------------------------------
        // 4. ARQUIVOS RELEVANTES
        // -----------------------------------------------------
        $fileList = [
            'Dockerfile' => base_path('Dockerfile'),
            'AppServiceProvider.php' => app_path('Providers/AppServiceProvider.php'),
            'Kernel.php' => app_path('Http/Kernel.php'),
            'TrustProxies.php' => app_path('Http/Middleware/TrustProxies.php'),
            'VerifyCsrfToken.php' => app_path('Http/Middleware/VerifyCsrfToken.php'),
            'config/cors.php' => config_path('cors.php'),
            'routes/web.php' => base_path('routes/web.php'),
            'public/.htaccess' => public_path('.htaccess'),
        ];

        $fileContents = [];
        foreach ($fileList as $label => $path) {
            if (File::exists($path)) {
                $content = File::get($path);
                $content = preg_replace(
                    '/(APP_KEY|DB_PASSWORD|DB_USERNAME|DB_HOST|AWS_SECRET_ACCESS_KEY)=([^\n]+)/',
                    '$1=**********',
                    $content
                );
                $fileContents[$label] = $content;
            } else {
                $fileContents[$label] = '[Arquivo não encontrado]';
            }
        }

        // -----------------------------------------------------
        // 5. Renderização da view
        // -----------------------------------------------------
        return view('diag.index', [
            'status' => $status,
            'env' => $envMasked,
            'cors' => $corsConfig,
            'files' => $fileContents,
        ]);
    }

    public function cookieTest()
    {
        return response('ok')
            ->cookie('probe', '1', 0, null, null, true, true, false, 'None');
    }
}
