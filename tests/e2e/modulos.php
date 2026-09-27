<?php
// Emite en JSON las páginas índice de cada módulo del registry config/modulos.php
// (el mismo que arma el sidebar y los permisos): un módulo nuevo entra solo al smoke.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$paginas = [];
foreach (array_keys(config('modulos')) as $modulo) {
    $nombre = "{$modulo}.index";
    $ruta = app('router')->getRoutes()->getByName($nombre);
    if ($ruta && in_array('GET', $ruta->methods()) && ! str_contains($ruta->uri(), '{')) {
        $paginas[] = ['modulo' => $modulo, 'path' => '/'.ltrim($ruta->uri(), '/')];
    }
}
echo json_encode($paginas, JSON_UNESCAPED_SLASHES);
