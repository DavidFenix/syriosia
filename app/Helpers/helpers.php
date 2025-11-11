<?php

use Illuminate\Support\Facades\Route;

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
function img_to_base64($localPath)
{
    if (!file_exists($localPath) || !is_readable($localPath)) {
        return null; // fallback será tratado no Blade
    }

    $mime = mime_content_type($localPath);
    $data = base64_encode(file_get_contents($localPath));

    return "data:$mime;base64,$data";
}

function img_to_svg_base64($path, $width = 80, $height = 80)
{
    if (!file_exists($path)) {
        return null;
    }

    $data = base64_encode(file_get_contents($path));
    $mime = mime_content_type($path);

    return "
        <svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>
            <image href='data:{$mime};base64,{$data}' width='{$width}' height='{$height}' />
        </svg>
    ";
}


function safe_image_base64($path)
{
    try {
        if (!file_exists($path)) {
            return null;
        }

        // Carrega imagem PNG, JPG OU WEBP
        $img = imagecreatefromstring(file_get_contents($path));
        if (!$img) {
            return null;
        }

        // Converte para JPG em buffer
        ob_start();
        imagejpeg($img, null, 90); // gera JPG limpo
        $jpgData = ob_get_clean();

        imagedestroy($img);

        if (!$jpgData) {
            return null;
        }

        // Base64 com MIME correto
        $base64 = base64_encode($jpgData);
        return 'data:image/jpeg;base64,' . $base64;

    } catch (\Throwable $e) {
        return null;
    }
}

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