<?php

// Laravel's default serve command selects .env.{APP_ENV} when that file
// exists. Vercel CLI creates .env.local for its OIDC token, so preload the
// application's local .env before the built-in server handles a request.
// This file is used only by `php artisan serve`; Vercel uses api/index.php.
require_once __DIR__.'/vendor/autoload.php';

Dotenv\Dotenv::createUnsafeMutable(__DIR__, '.env')->safeLoad();

chdir(__DIR__.'/public');

return require __DIR__.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
