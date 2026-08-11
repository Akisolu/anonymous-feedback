<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client as RedisClient;

use Akisolu\AnonymousFeedback\Services\RateLimiter;

$config = require __DIR__ . '/config.php';

$builder = new ContainerBuilder();
$builder->addDefinitions([
    'config' => $config,
    PDO::class => function (ContainerInterface $c) {
        $db = $c->get('config')['db'];
        $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['database']}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, $db['username'], $db['password'], $options);
    },
    Capsule::class => function (ContainerInterface $c) {
        $capsule = new Capsule();
        $capsule->addConnection($c->get('config')['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    },
    RedisClient::class => function (ContainerInterface $c) {
        return new RedisClient($c->get('config')['redis']);
    },
    RateLimiter::class => function (ContainerInterface $c) {
        return new RateLimiter($c->get(RedisClient::class));
    },
]);

return $builder->build();
