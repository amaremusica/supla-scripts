<?php

namespace suplascripts;

use suplascripts\app\Application;
use suplascripts\controllers\DevicesController;
use suplascripts\controllers\SystemController;
use suplascripts\controllers\TokensController;
use suplascripts\controllers\UsersController;

require __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 'Off');
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/var/logs/error.log");

$app = new Application();
$app->group('/api', function () use ($app) {
    $app->get('/time', SystemController::class . ':getTime');
    $app->group('/tokens', function () use ($app) {
        $app->post('', TokensController::class . ':createToken');
        $app->put('', TokensController::class . ':refreshToken');
    });
    $app->group('/users', function () use ($app) {
        $app->post('', UsersController::class . ':post');
        $app->get('/{id}', UsersController::class . ':get');
        $app->patch('/{id}', UsersController::class . ':patch');
        $app->put('/{id}', UsersController::class . ':put');
        $app->delete('/{id}', UsersController::class . ':delete');
    });
    $app->group('/devices', function () use ($app) {
        $app->get('', DevicesController::class . ':getList');
    });
});
$app->run();