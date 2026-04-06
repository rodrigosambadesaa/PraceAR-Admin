<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'admin/ajax_quick_edit.php',
            'admin/ajax_quick_edit_save.php',
            'ajax/generate_password.php',
            'helpers/verify_malicious_photo.php',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
