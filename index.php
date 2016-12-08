<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework.
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1.5
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 * -------------------------------------------------------- */
 
// define the api timezone 
date_default_timezone_set('Asia/Tokyo');

// import loader ...  
if (!require_once('seed/loader.php')) {
  die("Loader not found! Aborted.");
}

$app = Seed\App::getInstance(); 

// @use /sample/anything
$app->route('GET /sample/([a-z]+)', function () use ($app) {
  echo 
    '<pre >',
    'Arguments can be retrieved like this:', "\n",
    var_dump( $app->request() ),
    '</pre>';
});

// @use /sample/100/your/name
$app->route('GET /sample/([0-9]+)/([a-z]+)(/[a-z]+){0,1}', function ( $args ) {
  echo 
    '<pre >',
    'Arguments can be retrieved like this:', "\n",
    var_dump( $args ),
    '</pre>';
});

// @use /welcome
$app->route('GET /xml', function () use ($app) {
  $app->response(200, ['message' => 'You are very welcome!', 'output' => 'xml'], 'xml');
});

// @use /welcome
$app->route('GET /welcome', function () use ($app) {
  $app->response(200, ['message' => 'You are very welcome!']);
});

// @use /
$app->route('GET /', function () use ($app) {
  header("location: {$app->request()->base}/welcome", 302);
});

$app->run();
