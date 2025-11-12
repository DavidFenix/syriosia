<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        // 🔧 Configurações gerais
        Schema::defaultStringLength(191);
        date_default_timezone_set(config('app.timezone'));
        Carbon::setLocale(config('app.locale'));
        Paginator::defaultView('vendor.pagination.default');

        /*
        |--------------------------------------------------------------------------
        | Força HTTPS somente quando houver proxy indicando isso
        |--------------------------------------------------------------------------
        */
        if ($this->app->environment('production')) {
            if (request()->header('x-forwarded-proto') === 'https') {
                URL::forceScheme('https');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Criação automática do symlink storage → public/storage
        | Somente em produção e somente se ainda não existir
        |--------------------------------------------------------------------------
        */
        if ($this->app->environment('production')) {
            $public = public_path('storage');
            $target = storage_path('app/public');

            // Se o link ainda NÃO existir
            if (!is_link($public)) {
                try {
                    // garante que o diretório de destino existe
                    if (!is_dir($target)) {
                        @mkdir($target, 0755, true);
                    }

                    // cria o link
                    symlink($target, $public);
                } catch (\Throwable $e) {
                    // silencioso para não quebrar o sistema
                    // railway não permite mkdir em certas horas
                }
            }
        }
    }
}
