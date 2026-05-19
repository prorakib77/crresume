<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$sharedHostingBase = realpath(__DIR__ . '/../laravel_app');
$appBasePath = is_string($sharedHostingBase) && is_file($sharedHostingBase . '/bootstrap/app.php')
    ? $sharedHostingBase
    : __DIR__;

if (file_exists($maintenance = $appBasePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appBasePath . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appBasePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
