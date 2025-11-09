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