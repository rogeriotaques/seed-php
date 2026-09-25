<?php

/**
 * Seed-PHP test fixture.
 * A dependency-free router used by tests.php to exercise the framework over HTTP.
 * It is not an application entrypoint.
 */

error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/../loader.php';

$app = SeedPHP\App::getInstance();

$app->setCache(false);

$app->route('GET /', function () {
    echo 'root';
});

$app->route('GET /json', function () use ($app) {
    return $app->response(200, ['message' => 'ok', 'data' => ['foo' => 'bar']]);
});

$app->route('GET /xml', function () use ($app) {
    return $app->response(200, ['message' => 'ok'], 'xml');
});

$app->route('GET /error', function () use ($app) {
    return $app->response(404, ['message' => 'nope']);
});

$app->route('GET /hooks', function () {
    echo 'main';
}, function () {
    echo 'before|';
}, function () {
    echo '|after';
});

$app->route('GET|POST /methods', function () use ($app) {
    return $app->response(200, ['method' => $app->request()->method]);
});

$app->route('GET /sample(/\d+)?', function ($args) use ($app) {
    return $app->response(200, ['id' => $app->request()->id, 'args' => $args]);
});

$app->route('GET /ratelimit', function () use ($app) {
    $app->ratelimit->test();
    return $app->response(200, ['ok' => true]);
});

$app->load('ratelimit', [
    'limit_per_second' => 1,
    'limit_per_minute' => 1,
    'limit_per_hour' => 1,
    'seconds_banned' => 1,
    'seconds_delay' => 0,
]);

$app->run();
