<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DiagPdfController extends Controller
{
    public function full()
    {
        $report = [];

        // ============================
        // 1. Verificar imagens reais
        // ============================
        $paths = [
            'logo' => public_path('storage/logos/ubiratan.png'),
            'foto' => public_path('storage/img-user/padrao.png'),
        ];

        foreach ($paths as $key => $file) {
            $exists = file_exists($file);
            $size   = $exists ? filesize($file) : 0;
            $mime   = $exists ? mime_content_type($file) : null;

            $b64    = $exists ? ("data:" . $mime . ";base64," . base64_encode(file_get_contents($file))) : null;

            $report['files'][$key] = [
                'path' => $file,
                'exists' => $exists,
                'size' => $size,
                'mime' => $mime,
                'base64_sample' => $b64 ? substr($b64, 0, 80) . "..." : null,
            ];
        }

        // ============================
        // 2. Gerar HTML de teste
        // ============================
        $html = view('diag.pdf_test', [
            'logoBase64' => $report['files']['logo']['base64_sample'],
            'fotoBase64' => $report['files']['foto']['base64_sample'],
        ])->render();

        $report['html_first_200_chars'] = substr($html, 0, 200) . "...";

        // ============================
        // 3. Tentar gerar PDF real
        // ============================
        try {
            $pdf = Pdf::loadHTML($html)->setPaper('a4');
            $content = $pdf->output();

            $report['pdf'] = [
                'generated' => true,
                'size' => strlen($content),
                'sample_hex' => bin2hex(substr($content, 0, 30)),
            ];

            Storage::put('diag/full_pdf_test.pdf', $content);

        } catch (\Throwable $e) {
            $report['pdf'] = [
                'generated' => false,
                'error' => $e->getMessage(),
            ];
        }

        // ============================
        // 4. Devolver JSON detalhado
        // ============================
        return response()->json($report);
    }
}
