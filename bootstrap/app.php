<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

// use Dotenv\Dotenv;

// $appEnv = $_SERVER['APP_ENV'] ?? 'local';

// $basePath = dirname(__DIR__);

// if ($appEnv === 'docker' && file_exists($basePath . '/.env.docker')) {
//     Dotenv::createImmutable($basePath, '.env.docker')->load();
// } elseif ($appEnv === 'local' && file_exists($basePath . '/.env.local')) {
//     Dotenv::createImmutable($basePath, '.env.local')->load();
// }

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->append(\App\Http\Middleware\PrometheusMiddleware::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'optional.sanctum' => \App\Http\Middleware\OptionalSanctum::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {

        $schedule->command('rappels:envoyer')
            ->everyMinute()
            ->withoutOverlapping();
    })
    ->create();
