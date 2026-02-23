<?php

declare(strict_types=1);

if (getenv('VERCEL_ENV') || getenv('VERCEL_URL')) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

/**
 * Serve static assets directly from /public on Vercel.
 * This keeps Vite build files, robots.txt, and favicon reachable.
 */
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicRoot = realpath(__DIR__.'/../public');

if ($publicRoot !== false && $requestPath !== '/') {
    $relativePath = ltrim(rawurldecode($requestPath), '/');
    $candidate = realpath($publicRoot.DIRECTORY_SEPARATOR.$relativePath);

    if (
        $candidate !== false &&
        str_starts_with($candidate, $publicRoot.DIRECTORY_SEPARATOR) &&
        is_file($candidate)
    ) {
        $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'css' => 'text/css; charset=utf-8',
            'js', 'mjs' => 'application/javascript; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            default => (function (string $file): string {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected = $finfo ? (finfo_file($finfo, $file) ?: 'application/octet-stream') : 'application/octet-stream';
                if ($finfo) {
                    finfo_close($finfo);
                }
                return $detected;
            })($candidate),
        };

        header('Content-Type: '.$mimeType);
        header('Content-Length: '.(string) filesize($candidate));

        if (str_starts_with($relativePath, 'build/')) {
            header('Cache-Control: public, max-age=31536000, immutable');
        } else {
            header('Cache-Control: public, max-age=3600');
        }

        readfile($candidate);
        exit;
    }
}

// Route dynamic requests through Laravel's public front controller.
require __DIR__.'/../public/index.php';
