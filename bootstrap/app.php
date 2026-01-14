<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Run daily at 18:00 (6 PM) to mark missing attendances
        $schedule->command('attendance:mark-missing')->dailyAt('18:00');
        
        // Run sync for permissions every hour
        $schedule->command('sync:permission-attendances')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
