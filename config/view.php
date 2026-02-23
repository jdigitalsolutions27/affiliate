<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most applications have only one path where Blade templates are stored,
    | but you may specify multiple locations if needed.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | On Vercel, the project filesystem is read-only at runtime, so compiled
    | Blade templates are written to /tmp. Other environments keep default
    | Laravel behavior under storage/framework/views.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        env('VERCEL') ? '/tmp' : realpath(storage_path('framework/views'))
    ),

];

