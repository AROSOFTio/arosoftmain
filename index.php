<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Root Fallback Entrypoint
|--------------------------------------------------------------------------
|
| This allows the app to boot when a panel is temporarily configured to
| use the project root as document root instead of the /public directory.
|
*/

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

if (! file_exists(__DIR__.'/vendor/autoload.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Deployment incomplete.\n";
    echo "Missing file: /vendor/autoload.php\n\n";
    echo "Run deploy script on server:\n";
    echo "bash /www/wwwroot/arosoft.io/deploy/aapanel-deploy.sh\n";
    exit;
}

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
