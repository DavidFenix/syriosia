<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class DiagController extends Controller
{
    public function storage()
    {
        $results = [
            'symlink_exists' => false,
            'symlink_points_to' => null,
            'public_path_exists' => false,
            'storage_path_exists' => false,
            'write_test' => false,
            'read_test' => false,
            'delete_test' => false,
            'disk_root' => null,
            'errors' => [],
        ];

        try {
            // Verifica se o symlink existe
            $publicStorage = public_path('storage');
            $results['symlink_exists'] = file_exists($publicStorage);
            $results['public_path_exists'] = is_dir($publicStorage);

            // Verifica aonde o symlink aponta
            if ($results['symlink_exists']) {
                $results['symlink_points_to'] = realpath($publicStorage);
            }

            // Verifica se storage/app/public existe
            $results['storage_path_exists'] = is_dir(storage_path('app/public'));

            // Usar Storage Laravel
            $disk = Storage::disk('public');
            $results['disk_root'] = $disk->path('');

            // Testar escrita
            $testFile = 'diag_test.txt';
            $content = "Teste de escrita em " . now();

            if ($disk->put($testFile, $content)) {
                $results['write_test'] = true;

                // Testar leitura
                $read = $disk->get($testFile);
                $results['read_test'] = ($read === $content);

                // Testar delete
                $results['delete_test'] = $disk->delete($testFile);
            }

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }

        return view('diag.storage', compact('results'));
    }


    public function index(Request $request)
    {
        // -----------------------------------------------------
        // 1. STATUS DO AMBIENTE
        // -----------------------------------------------------
        $isHttps = $request->isSecure() || $request->header('x-forwarded-proto') === 'https';
        $appEnv = env('APP_ENV', 'unknown');
        $appUrl = env('APP_URL', '');
        $cookieTest = isset($_COOKIE['probe']);
        //$secureCookie = env('SESSION_SECURE_COOKIE');
        $secureCookie = config('session.secure');

        $status = [
            'railway' => $appUrl && str_contains($appUrl, 'railway.app'),
            'https' => $isHttps,
            'cookie_received' => $cookieTest,
            'secure_cookie' => config('session.secure'),
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

    public function indexini(Request $request)
    {
        if (!session()->isStarted()) {
            session()->start();
        }

        session()->put('diag_time', now()->toDateTimeString());

        return response()->json([
            'timestamp' => now()->toDateTimeString(),
            'https'     => $request->isSecure(),
            'forwarded_proto' => $request->header('x-forwarded-proto'),
            'session_id' => session()->getId(),
            'session_value' => session('diag_time'),
            'cookies'   => $request->cookies->all(),
            'headers'   => $request->headers->all(),
            'env'       => [
                'APP_ENV' => env('APP_ENV'),
                'APP_URL' => env('APP_URL'),
                'SESSION_DOMAIN' => env('SESSION_DOMAIN'),
                'SESSION_SECURE_COOKIE' => env('SESSION_SECURE_COOKIE'),
            ],
        ]);
    }

    public function cookieTestini()
    {
        return response()->json(['cookie' => 'sent'])
            ->cookie(
                'probe',
                'ok',
                10,
                '/',
                null,
                true,
                true,
                false,
                'None'
            );
    }
}
