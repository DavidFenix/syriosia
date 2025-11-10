<?php

use Illuminate\Support\Facades\App;

/**
 * Download seguro e universal para PDFs
 * - funciona no Railway (FrankenPHP)
 * - funciona no Apache/Nginx
 * - desativa gzip automaticamente
 * - evita PDFs corrompidos/0 bytes
 */
function pdf_download($filename, $content)
{
    // 1. Desativa compressão (universal)
    if (function_exists('ini_set')) {
        ini_set('zlib.output_compression', '0');
        ini_set('output_buffering', 'off');
    }

    // 2. Apenas se Apache existir
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', '1');
        @apache_setenv('dont-vary', '1');
    }

    // 3. Se estiver em FrankenPHP (Railway)
    $isFranken = App::runningInConsole()
        ? false
        : str_contains($_SERVER['SERVER_SOFTWARE'] ?? '', 'FrankenPHP');

    $headers = [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Content-Length'      => strlen($content),
        'Cache-Control'       => 'private, no-transform',
    ];

    if ($isFranken) {
        // Railway requer explicitly identity + no encoding
        $headers['Content-Encoding']   = 'identity';
        $headers['Transfer-Encoding']  = 'identity';
    }

    return response()->streamDownload(function () use ($content) {
        echo $content;
        flush();
    }, $filename, $headers);
}
