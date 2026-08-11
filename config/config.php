<?php

declare(strict_types = 1);

$baseDir = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($baseDir);
$dotenv->safeLoad();


return [
    "app" => [
        "name" => $_ENV['APP_NAME'] ?? 'MyApp',
        "env" => $_ENV['APP_ENV'] ?? 'production',
        "debug" => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
        "url" => $_ENV['APP_URL'] ?? 'http://localhost',
    ],

    "db" => [
        "driver" => $_ENV['DB_DRIVER'] ?? 'pgsql',
        "host" => $_ENV['DB_HOST'] ?? 'localhost',
        "port" => (int) ($_ENV['DB_PORT'] ?? 5432),
        "database" => $_ENV['DB_DATABASE'] ?? 'myapp',  
        "username" => $_ENV['DB_USERNAME'] ?? 'myapp',
        "password" => $_ENV['DB_PASSWORD'] ?? 'myapp',
        "charset" => $_ENV['DB_CHARSET'] ?? 'utf8',
        "schema" => $_ENV['DB_SCHEMA'] ?? 'public',

    ],

    "redis" => [
        "scheme" => $_ENV['REDIS_SCHEME'] ?? 'tcp',
        "host" => $_ENV['REDIS_HOST'] ?? 'localhost',
        "port" => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    ],

    "rate_limit" => [
        "max_requests" => (int) ($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 10),
        "decay" => (int) ($_ENV['RATE_LIMIT_DECAY'] ?? 600),
    ],
];
