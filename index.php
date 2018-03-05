<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework.
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.7.0
 | @license MIT
 | @see http://github.com/abtzco/seed-php
 * -------------------------------------------------------- */
 
// define the api timezone 
date_default_timezone_set('Asia/Tokyo');

// import loader ...  
if (!require_once('seed/loader.php')) {
  die("Loader not found! Aborted.");
}

$app = Seed\App::getInstance(); 

// force app to not do page caching (optional) 
$app->setCache(false);

// set an error handler (optional) 
$app->onFail( function ( $error ) {

  echo '<h1>Oops! An error has happened! </h1>';
  echo "<p >The error sais: {$error->code} - {$error->message}</p>";

  echo 
    '<pre >',
    'This is what we give to your error handler:', "\n",
    var_dump( $error ),
    '</pre>';
} );

// @use /sample/foo or /sample/foo/bar
$app->route('GET /sample/(\w+)(/\w+)?', function ( $args ) {
  echo 
    '<pre >',
    'Arguments can be retrieved like this:', "\n",
    var_dump( $args ),
    '</pre>';
});

// // @use /sample or /sample/100
$app->route('GET /sample(/\d+)?', function () use ($app) {
  echo 
    '<pre >',
    'Arguments can be retrieved like this:', "\n",
    var_dump( $app->request() ),
    '</pre>';
});

// @use /welcome
$app->route('GET /xml', function () use ($app) {
  $app->response(200, ['message' => 'You are very welcome!', 'output' => 'xml'], 'xml');
});

// @use /welcome
$app->route('GET /welcome', function () use ($app) {
  $app->response(200, ['message' => 'You are very welcome!', 'data' => [ 'foo' => 'bar' ] ]);
});

// @use /
$app->route('GET /', function () use ($app) {
  header("location: {$app->request()->base}/welcome", 302);
});

$app->run();
