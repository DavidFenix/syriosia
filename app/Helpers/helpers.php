<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

if (!function_exists('prefix')) {
    function prefix(string $basename = ''): string
    {
        $prefix = config('prefix.tabelas', 'syrios_');
        return $prefix . $basename;
    }
}

if (!function_exists('dashboard_route')) {
    function dashboard_route()
    {
        $user = auth()->user();

        if (!$user) {
            return route('login');
        }

        if (!session('current_role')) {
            return route('login');
        }

        $role = session('current_role');
        $schoolId = session('current_school_id');

        if ($role && $schoolId) {
            switch ($role) {
                case 'master':
                    return route('master.dashboard');
                case 'secretaria':
                    return route('secretaria.dashboard');
                case 'escola':
                    return route('escola.dashboard');
                case 'professor':
                    return route('professor.dashboard');
                default:
                    return '/';
            }
        }

        if ($user->hasRole('master')) {
            return route('master.dashboard');
        }

        if ($user->hasRole('secretaria')) {
            return route('secretaria.dashboard');
        }

        if ($user->hasRole('escola')) {
            return route('escola.dashboard');
        }

        if ($user->hasRole('professor')) {
            return route('professor.dashboard');
        }

        return route('choose.school');
    }
}

/**
 * Converte uma imagem local em Base64 para uso seguro no DomPDF.
 */
// function img_to_base64($localPath)
    // {
    //     if (!file_exists($localPath) || !is_readable($localPath)) {
    //         return null; // fallback será tratado no Blade
    //     }

    //     $mime = mime_content_type($localPath);
    //     $data = base64_encode(file_get_contents($localPath));

    //     return "data:$mime;base64,$data";
    // }

    // function img_to_svg_base64($path, $width = 80, $height = 80)
    // {
    //     if (!file_exists($path)) {
    //         return null;
    //     }

    //     $data = base64_encode(file_get_contents($path));
    //     $mime = mime_content_type($path);

    //     return "
    //         <svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>
    //             <image href='data:{$mime};base64,{$data}' width='{$width}' height='{$height}' />
    //         </svg>
    //     ";
    // }


    // function safe_image_base64($path)
    // {
    //     try {
    //         if (!file_exists($path)) {
    //             return null;
    //         }

    //         // Carrega imagem PNG, JPG OU WEBP
    //         $img = imagecreatefromstring(file_get_contents($path));
    //         if (!$img) {
    //             return null;
    //         }

    //         // Converte para JPG em buffer
    //         ob_start();
    //         imagejpeg($img, null, 90); // gera JPG limpo
    //         $jpgData = ob_get_clean();

    //         imagedestroy($img);

    //         if (!$jpgData) {
    //             return null;
    //         }

    //         // Base64 com MIME correto
    //         $base64 = base64_encode($jpgData);
    //         return 'data:image/jpeg;base64,' . $base64;

    //     } catch (\Throwable $e) {
    //         return null;
    //     }
    // }

    // function safe_image_base64($path)
    // {
    //     if (!file_exists($path)) {
    //         return null;
    //     }

    //     try {
    //         $imgData = file_get_contents($path);
    //         $info = getimagesize($path);

    //         // Se for PNG e potencialmente problemático, converter
    //         if ($info && $info['mime'] === 'image/png') {

    //             // Cria imagem a partir do PNG
    //             $image = imagecreatefrompng($path);
    //             if (!$image) return null;

    //             // Remove transparência (fundo branco)
    //             $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
    //             $white = imagecolorallocate($bg, 255, 255, 255);
    //             imagefill($bg, 0, 0, $white);
    //             imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

    //             // Converte para JPG em memória
    //             ob_start();
    //             imagejpeg($bg, null, 92);
    //             $jpgData = ob_get_clean();

    //             return 'data:image/jpeg;base64,' . base64_encode($jpgData);
    //         }

    //         // Outros formatos OK
    //         return 'data:' . $info['mime'] . ';base64,' . base64_encode($imgData);

    //     } catch (\Throwable $e) {
    //         return null;
    //     }
// }




if (!function_exists('sql_dump')) {
    /**
     * Exibe o SQL real de uma query Eloquent/Builder, log de queries ou coleção.
     * Compatível com Laravel 8–11 e prefixos dinâmicos.
     */
    function sql_dump($input, bool $die = true)
    {
        // 🧠 Caso 1: Eloquent ou Query Builder
        if ($input instanceof \Illuminate\Database\Eloquent\Builder ||
            $input instanceof \Illuminate\Database\Query\Builder) {

            $sql = $input->toSql();
            $bindings = $input->getBindings();

            foreach ($bindings as $binding) {
                $binding = is_numeric($binding)
                    ? $binding
                    : "'" . addslashes($binding) . "'";
                $sql = preg_replace('/\?/', $binding, $sql, 1);
            }

            // 🌈 Colorir palavras-chave SQL (para uso em terminal artisan ou logs)
            $ansiSql = preg_replace([
                '/\b(SELECT|FROM|WHERE|EXISTS|INNER JOIN|LEFT JOIN|ON|AND|OR|INSERT|UPDATE|DELETE|VALUES|INTO)\b/i'
            ], [
                "\033[1;34m$1\033[0m" // azul negrito
            ], $sql);

            echo "\n\n========== 🧠 SQL DUMP ==========\n";
            echo $sql . "\n";
            echo "=================================\n\n";
            return $die ? dd($sql) : dump($sql);
        }

        // 🧠 Caso 2: Log de queries (via DB::enableQueryLog())
        if (is_array($input) && isset($input[0]['query'])) {
            echo "\n\n========== 🧠 QUERY LOG ==========\n";
            foreach ($input as $log) {
                $query = $log['query'];
                foreach ($log['bindings'] as $binding) {
                    $binding = is_numeric($binding)
                        ? $binding
                        : "'" . addslashes($binding) . "'";
                    $query = preg_replace('/\?/', $binding, $query, 1);
                }
                echo $query . ";\n";
            }
            echo "=================================\n\n";
            return $die ? dd('✅ Log exibido') : null;
        }

        // 🧠 Caso 3: Collection (apenas mostra dados)
        if ($input instanceof \Illuminate\Support\Collection) {
            dump($input->toArray());
            return;
        }

        // 🧠 Caso 4: String SQL direta
        if (is_string($input)) {
            echo "\n========== 🧠 SQL RAW ==========\n";
            echo $input . "\n";
            echo "=================================\n";
            return $die ? dd($input) : dump($input);
        }

        // 🚨 Nenhum caso conhecido
        dd([
            'erro' => '⚠️ Tipo de entrada não reconhecido',
            'tipo' => is_object($input) ? get_class($input) : gettype($input),
            'valor' => $input,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| DETECÇÃO DO SERVIDOR
|--------------------------------------------------------------------------
*/

if (!function_exists('is_infinityfree')) {
    function is_infinityfree() {
        // 1º: se houver flag explícita no .env, ela manda em tudo
        $flag = env('SYRIOS_INFINITYFREE');
        if (!is_null($flag)) {
            return (bool)$flag;
        }

        // 2º: fallback automático (caso você queira manter)
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $root = $_SERVER['DOCUMENT_ROOT'] ?? '';

        return str_contains($host, 'rf.gd')
            || str_contains($host, 'epizy.com')
            || str_contains($root, '/htdocs/');
    }
}


// if (!function_exists('is_infinityfree')) {
//     function is_infinityfree() {
//         $host = $_SERVER['HTTP_HOST'] ?? '';
//         $root = $_SERVER['DOCUMENT_ROOT'] ?? '';

//         return str_contains($host, 'rf.gd')
//             || str_contains($host, 'epizy.com')
//             || str_contains($root, '/htdocs/');
//     }
// }


/*
|--------------------------------------------------------------------------
| CAMINHOS DE ARQUIVOS DO STORAGE (ABSOLUTO E URL)
|--------------------------------------------------------------------------
*/

// //guardando aqui pois vou fazer mudança perigosa
// if (!function_exists('storage_syrios_path')) {
//     /**
//      * Retorna o caminho ABSOLUTO de um arquivo dentro de storage/app/public
//      * Ex: storage_syrios_path("img-user/123.png")
//      */
//     function storage_syrios_path(string $path): string
//     {
//         return storage_path('app/public/' . ltrim($path, '/'));
//     }
// }

// //guardando aqui pois vou fazer mudança perigosa
// if (!function_exists('storage_syrios_url')) {
//     /**
//      * Retorna a URL pública correta do arquivo em QUALQUER servidor:
//      * Railway → /storage/arquivo.png
//      * InfinityFree → /syriosia/storage/app/public/arquivo.png
//      */
//     function storage_syrios_url(string $path): string
//     {
//         $path = ltrim($path, '/');

//         // InfinityFree sempre precisa do prefixo "syriosia/storage/app/public"
//         if (is_infinityfree()) {
//             return url("syriosia/storage/app/public/{$path}");
//         }

//         // Railway, WAMP, VPS (symlink funcionando)
//         return url("storage/{$path}");
//     }
// }

if (!function_exists('storage_syrios_path')) {
    function storage_syrios_path(string $path): string
    {
        // garante que "storage/" nunca apareça duplicado
        $path = ltrim($path, '/');

        // se vier "storage/logos/x.png", troca para "logos/x.png"
        $path = preg_replace('#^storage/#', '', $path);

        return storage_path('app/public/' . $path);
    }
}

if (!function_exists('storage_syrios_url')) {
    function storage_syrios_url(string $path): string
    {
        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#', '', $path); // remove duplicações

        if (is_infinityfree()) {
            // Sempre monta URL completa para bypass do .htaccess do InfinityFree
            return url("syriosia/storage/app/public/{$path}");
        }

        return url("storage/{$path}");
    }
}



/*
|--------------------------------------------------------------------------
| LISTA GLOBAL DE EXTENSÕES PERMITIDAS
|--------------------------------------------------------------------------
*/
if (!function_exists('syrios_valid_extensions')) {
    function syrios_valid_extensions()
    {
        return ['png', 'jpg', 'jpeg', 'webp'];
    }
}


/*
|--------------------------------------------------------------------------
| LOCALIZAR CAMINHO FÍSICO DE ARQUIVO POR EXTENSÃO
|--------------------------------------------------------------------------
*/

if (!function_exists('syrios_find_file')) {
    /**
     * Procura um arquivo em storage/app/public/<folder>/<base>.<ext>
     * para QUALQUER extensão associada ao sistema.
     *
     * Retorna caminho absoluto OU null.
     */
    function syrios_find_file(string $folder, string $base)
    {
        foreach (syrios_valid_extensions() as $ext) {
            $relative = "{$folder}/{$base}.{$ext}";
            $absolute = storage_syrios_path($relative);

            if (file_exists($absolute)) {
                return $absolute;
            }
        }

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| FOTOS DE ALUNOS (URL e Path)
|--------------------------------------------------------------------------
*/

if (!function_exists('syrios_user_photo_path')) {
    /**
     * Retorna caminho ABSOLUTO da foto do aluno
     * ou o padrao.png.
     */
    function syrios_user_photo_path($matricula, $schoolId = null)
    {
        $schoolId = $schoolId ?: session('current_school_id');
        $base = "{$schoolId}_{$matricula}";

        $found = syrios_find_file("img-user", $base);

        return $found ?: storage_syrios_path("img-user/padrao.png");
    }
}

if (!function_exists('syrios_user_photo')) {
    /**
     * Retorna a URL PÚBLICA da foto do aluno usando o path real.
     */
    function syrios_user_photo($matricula, $schoolId = null)
    {
        $schoolId = $schoolId ?: session('current_school_id');
        $base = "{$schoolId}_{$matricula}";

        // tenta localizar arquivo real por extensão
        foreach (syrios_valid_extensions() as $ext) {
            $relative = "img-user/{$base}.{$ext}";
            if (file_exists(storage_syrios_path($relative))) {
                return storage_syrios_url($relative);
            }
        }

        // fallback
        return storage_syrios_url("img-user/padrao.png");
    }
}


/*
|--------------------------------------------------------------------------
| LOGO DAS ESCOLAS (URL)
|--------------------------------------------------------------------------
*/
if (!function_exists('syrios_school_logo')) {
    /**
     * Retorna URL da logo da escola (independente da extensão)
     * 
     * Caso schoolId = 0 → retorna a logo principal do sistema (syrios.png)
     */
    function syrios_school_logo($schoolId)
    {
        // Caso especial: logo principal do sistema
        if ((int)$schoolId === 0) {

            // tenta várias extensões: syrios.png / syrios.jpg / syrios.webp
            foreach (syrios_valid_extensions() as $ext) {
                $relative = "logos/syrios.{$ext}";

                if (file_exists(storage_syrios_path($relative))) {
                    return storage_syrios_url($relative);
                }
            }

            // fallback final (deveria existir sempre)
            return storage_syrios_url("logos/syrios.png");
        }

        // Logo normal de escola
        $base = "{$schoolId}_logo";

        foreach (syrios_valid_extensions() as $ext) {
            $relative = "logos/{$base}.{$ext}";

            if (file_exists(storage_syrios_path($relative))) {
                return storage_syrios_url($relative);
            }
        }

        // fallback
        return storage_syrios_url("logos/syrios.png");
    }
}


/**
 * Converte uma URL pública vinda de storage_syrios_url()
 * para um caminho relativo dentro de storage/app/public.
 *
 * Remove automaticamente todos os prefixos possíveis
 * em Railway, WAMP, InfinityFree, subdiretórios etc.
 */
if (!function_exists('syrios_url_to_storage_relative')) {
    function syrios_url_to_storage_relative(string $url): string
    {
        // remove domínio
        $clean = str_replace(url('/') . '/', '', $url);

        // tratamento ESPECIAL = só se InfinityFree
        if (is_infinityfree()) {
            // remove swriosia/storage/app/public/
            $clean = preg_replace('#^.*/storage/app/public/#', '', $clean);
        } else {
            // Railway/WAMP - remove somente "storage/"
            $clean = preg_replace('#^storage/#', '', $clean);
        }

        return ltrim($clean, '/');
    }
}
// if (!function_exists('syrios_url_to_storage_relative')) {
//     function syrios_url_to_storage_relative(string $url): string
//     {
//         // 1) Remove domínio e subpastas até chegar no path interno
//         $clean = str_replace(url('/') . '/', '', $url);

//         // 2) Remove o prefixo InfinityFree ("syriosia/storage/app/public/")
//         $clean = preg_replace('#^.*/storage/app/public/#', '', $clean);

//         // 3) Remove o prefixo Railway/Localhost ("storage/")
//         $clean = preg_replace('#^storage/#', '', $clean);

//         return ltrim($clean, '/');
//     }
// }



