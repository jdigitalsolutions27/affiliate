<?php

declare(strict_types=1);

if (getenv('VERCEL_ENV') || getenv('VERCEL_URL')) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

// Route all dynamic requests through Laravel's public front controller.
require __DIR__.'/../public/index.php';
