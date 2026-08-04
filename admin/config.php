<?php
declare(strict_types=1);

return [
    'site_name' => 'sechskies_fans',
    'db' => [
        'host' => getenv('SECHSKIES_FANS_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('SECHSKIES_FANS_DB_PORT') ?: '3306',
        'name' => getenv('SECHSKIES_FANS_DB_NAME') ?: 'sechskies_fans',
        'user' => getenv('SECHSKIES_FANS_DB_USER') ?: 'root',
        'pass' => getenv('SECHSKIES_FANS_DB_PASS') ?: '',
    ],
    'dance_upload_dir' => dirname(__DIR__) . '/storage/dance-applications',
    'dance_max_upload_bytes' => 8 * 1024 * 1024,
];
