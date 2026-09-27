<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Guard: RefreshDatabase borra la BD. Si la config está cacheada
        // (bootstrap/cache/config.php) o phpunit.xml no aplica, abortar
        // antes de tocar la BD de desarrollo.
        $db = $app['config']->get('database.connections.'.$app['config']->get('database.default').'.database');
        if (! str_ends_with((string) $db, '_test')) {
            throw new \RuntimeException("Tests abortados: la BD '{$db}' no es de pruebas (debe terminar en _test). Ejecuta `php artisan config:clear`.");
        }

        return $app;
    }
}
