<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => RoleMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if (! $request->isMethod('POST') || ! $request->is('belajar/*/tugas/*')) {
                return null;
            }

            $maxFileKb = (int) config('eduka.assignment_upload.max_file_kb', 5120);
            $maxFileSize = $maxFileKb >= 1024 && $maxFileKb % 1024 === 0
                ? ($maxFileKb / 1024).' MB'
                : $maxFileKb.' KB';
            $message = 'Ukuran pengiriman tugas terlalu besar. Maksimal file yang dapat dikirim adalah '.$maxFileSize.'. Kurangi ukuran file lalu coba lagi.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => ['file' => [$message]]], 413);
            }

            return back()->withErrors(['file' => $message]);
        });
    })->create();
