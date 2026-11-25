<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        // Private files (uploaded documents). Served only through
        // authorized routes, never directly by the web server.
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],

        // Publicly reachable files (signature images). Exposed via `php artisan storage:link`.
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
