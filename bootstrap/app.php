<?php

use Dotenv\Dotenv;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Application;

// 1) Ręcznie wczytujemy **najpierw** .env, a potem .env.local (nadpisując wartości)
$basePath = dirname(__DIR__);
$dotenv = Dotenv::createImmutable($basePath, ['.env', '.env.local']);
$dotenv->safeLoad(); // safeLoad wczytuje zmienne środowiskowe, ale nie rzuca błędu, gdy plik .env nie istnieje

// 2) Teraz Laravel/Lumen/Zero może bezpiecznie skonfigurować aplikację,
//    już mając gotowe $_ENV i getenv() z obu plików.
return Application::configure(basePath: $basePath)
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',      // upewnij się, że jest
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'docs.auth' => \App\Http\Middleware\DocsAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
error_reporting(E_ALL);
ini_set('display_errors', 'On');
