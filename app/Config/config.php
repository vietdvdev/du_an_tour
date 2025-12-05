<?php
return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'PHP MVC Base PRO',
        'env' => getenv('APP_ENV') ?: 'local',
       'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'url' => getenv('APP_URL') ?: 'http://localhost/du_an_tour/du_an_tour/public/',
        'timezone' => getenv('TIMEZONE') ?: 'Asia/Bangkok',
        'key' => getenv('APP_KEY') ?: 'change-me',
    ],
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'tour_db',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
];
