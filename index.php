<?php

/**
 * Alternate Laravel front controller for hosting setups where the document root
 * cannot be pointed to the `public/` directory. Copy this file to your web root
 * (e.g., public_html/) and rename to `index.php`. Ensure the Laravel project
 * folders (`vendor/`, `bootstrap/`, `app/`, `storage/`, `.env`) sit alongside
 * this file in the same directory.
 */

define('LARAVEL_START', microtime(true));

// Autoload dependencies (path relative to web root)
require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application (path relative to web root)
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
