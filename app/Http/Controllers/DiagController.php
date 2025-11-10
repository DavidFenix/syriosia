<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class DiagController extends Controller
{
    
    //diagnóstico pdf
    public function pdfDiag()
    {
        $result = [
            'time' => now()->toDateTimeString(),
            'vendor_autoload' => file_exists(base_path('vendor/autoload.php')),
            'classes' => [
                'Barryvdh\\DomPDF\\Facade\\Pdf' => class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) ?? false,
                'Dompdf\\Dompdf' => class_exists(\Dompdf\Dompdf::class) ?? false,
            ],
            'php_settings' => [
                'memory_limit' => ini_get('memory_limit'),
                'output_buffering' => ini_get('output_buffering'),
                'upload_tmp_dir' => ini_get('upload_tmp_dir'),
                'sys_temp_dir' => sys_get_temp_dir(),
            ],
            'storage_writable' => is_writable(storage_path('app')) && is_writable(storage_path('app/public')),
            'attempts' => [],
        ];

        // HTML de teste minimal
        $html = '<h1>PDF Test</h1><p>Gerado em ' . now()->toDateTimeString() . '</p>';

        // 1) Teste com facade Barryvdh (se existir)
        try {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $bytes = $pdf->output();
                $path = 'diag/pdf_facade_test_' . time() . '.pdf';
                Storage::put($path, $bytes);
                $result['attempts']['facade'] = [
                    'ok' => true,
                    'file' => $path,
                    'filesize' => Storage::size($path),
                ];
            } else {
                $result['attempts']['facade'] = ['ok' => false, 'error' => 'class not found'];
            }
        } catch (\Throwable $e) {
            $result['attempts']['facade'] = ['ok' => false, 'exception' => $e->getMessage()];
            Log::error('pdfDiag facade error: '.$e->getMessage());
        }

        // 2) Teste com Dompdf direto
        try {
            if (class_exists(\Dompdf\Dompdf::class)) {
                $dompdf = new \Dompdf\Dompdf([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $bytes2 = $dompdf->output();
                $path2 = 'diag/pdf_dompdf_test_' . time() . '.pdf';
                Storage::put($path2, $bytes2);
                $result['attempts']['dompdf'] = [
                    'ok' => true,
                    'file' => $path2,
                    'filesize' => Storage::size($path2),
                    'warnings' => method_exists($dompdf, 'getWarnings') ? $dompdf->getWarnings() : null,
                ];
            } else {
                $result['attempts']['dompdf'] = ['ok' => false, 'error' => 'class not found'];
            }
        } catch (\Throwable $e) {
            $result['attempts']['dompdf'] = ['ok' => false, 'exception' => $e->getMessage()];
            Log::error('pdfDiag dompdf error: '.$e->getMessage());
        }

        // 3) Teste de streaming direto (simula controller que retorna o PDF)
        try {
            if (isset($bytes) && strlen($bytes) > 0) {
                // cria um arquivo e devolve headers para download
                $streamPath = storage_path('app/diag/pdf_stream_test_' . time() . '.pdf');
                file_put_contents($streamPath, $bytes);
                $result['attempts']['stream_file'] = [
                    'ok' => true,
                    'path' => $streamPath,
                    'filesize' => filesize($streamPath),
                ];
            } else {
                $result['attempts']['stream_file'] = ['ok' => false, 'error' => 'no bytes from previous attempts'];
            }
        } catch (\Throwable $e) {
            $result['attempts']['stream_file'] = ['ok' => false, 'exception' => $e->getMessage()];
            Log::error('pdfDiag stream error: '.$e->getMessage());
        }

        return response()->json($result);
    }

    //diagnóstico pdf
    public function pdfDownloadTest()
    {
        // gera um PDF simples e retorna como download (ou erro detalhado)
        $html = '<h1>PDF Download Test</h1><p>' . now()->toDateTimeString() . '</p>';

        try {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                return $pdf->download('diag_download_test.pdf');
            } elseif (class_exists(\Dompdf\Dompdf::class)) {
                $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $out = $dompdf->output();
                return response($out, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="diag_download_test.pdf"',
                    'Content-Length' => strlen($out),
                ]);
            } else {
                return response()->json(['error' => 'Nenhuma biblioteca de PDF encontrada'], 500);
            }
        } catch (\Throwable $e) {
            Log::error('pdfDownloadTest error: '.$e->getMessage());
            return response()->json(['exception' => $e->getMessage()], 500);
        }
    }

    //diagnóstico storage
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

    //diagnóstico cookie e sessão
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
