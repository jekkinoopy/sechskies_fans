<?php
declare(strict_types=1);

return [
    'site_name' => 'YELLOW WAVE',
    'db' => [
        'host' => getenv('YELLOWKIES_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('YELLOWKIES_DB_PORT') ?: '3306',
        'name' => getenv('YELLOWKIES_DB_NAME') ?: 'yellowkies',
        'user' => getenv('YELLOWKIES_DB_USER') ?: 'root',
        'pass' => getenv('YELLOWKIES_DB_PASS') ?: '',
    ],
    'dance_upload_dir' => dirname(__DIR__) . '/storage/dance-applications',
    'dance_max_upload_bytes' => 8 * 1024 * 1024,
];
