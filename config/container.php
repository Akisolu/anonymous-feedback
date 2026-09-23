<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client as RedisClient;

use Akisolu\AnonymousFeedback\Services\RateLimiter;
use Akisolu\AnonymousFeedback\Repositories\FeedbackRepositoryInterface;
use Akisolu\AnonymousFeedback\Repositories\FeedbackRepository;
use Akisolu\AnonymousFeedback\Controllers\FeedbackController;

$config = require __DIR__ . '/config.php';

$builder = new ContainerBuilder();
$builder->addDefinitions([
    'config' => $config,

    PDO::class => function (ContainerInterface $c) {
        $db = $c->get('config')['db'];
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s',
            $db['driver'],
            $db['host'],
            $db['port'],
            $db['database']
        );

        return new PDO(
            $dsn,
            $db['username'],
            $db['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
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

    FeedbackRepositoryInterface::class => function (ContainerInterface $c) {
        $c->get(Capsule::class);
        return new FeedbackRepository();
    },

    FeedbackController::class => function (ContainerInterface $c) {
        $c->get(Capsule::class);
        return new FeedbackController(
            $c->get(FeedbackRepositoryInterface::class),
            $c->get(RateLimiter::class)
        );
    },
]);

return $builder->build();
