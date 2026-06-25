<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Fix SCRIPT_NAME for subdirectory — disabled for production root-domain deployment
if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/public/')) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/public/', '/', $_SERVER['SCRIPT_NAME']);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
