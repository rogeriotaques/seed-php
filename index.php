<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework, TEST
 | @version 1.0.0
 * -------------------------------------------------------- */
 
// define the api timezone 
date_default_timezone_set('Asia/Tokyo');

// import loader ...  
if (!require_once('loader.php')) {
  require_once __DIR__ . '/loader.php';
}

function run_test_query($times = 0, $isPDO = false)
{
  global $app;

  $app->mysql->connect();

  if ($times === 0) {
    run_test_query(1, $isPDO);
  }

  if ($times === 1 && $isPDO) {
    $sql = 'select 1 from `projects` where `id`=:id';
    $res = $app->mysql->exec($sql, ['id' => 1]);
    echo 'Ran a query: ', var_dump($res), "<br >\n";
  }

  $res = $app->mysql->exec('select 1');
  echo 'Ran a query: ', var_dump($res), "<br >\n";

  $app->mysql->disconnect();
}

$app = SeedPHP\App::getInstance(); 

// force app to not do page caching (optional) 
$app->setCache(false);

// set an error handler (optional) 
$app->onFail(function ($error) {

  echo '<h1>Oops! An error has happened! </h1>';
  echo "<p >The error sais: {$error->code} - {$error->message}</p>";

  echo
    '<pre >',
    'This is what we give to your error handler:', "\n\n",
    var_dump($error),
    '</pre>';
});

// @use /database
$app->route('GET /database', function () use ($app) {
  $app->load(
    'mysql',
    [
      'host' => 'localhost',
      'port' => '3306',
      'user' => 'root',
      'pass' => '',
      'base' => 'issuer'
    ]
  );

  run_test_query();

  echo '<br >';

  $app->load(
    'pdo',
    [
      'host' => 'localhost',
      'port' => '3306',
      'user' => 'root',
      'pass' => '',
      'base' => 'issuer'
    ],
    'mysql'
  );

  run_test_query(0, true);
});

// @use /sample/foo or /sample/foo/bar or /sample/foo/bar/zoo
$app->route('GET /sample/(\w+)(/\w+)?(/\w+)?', function ($args) use ($app) {
  echo
    '<pre >',
    'Arguments can be retrieved like this:', "\n\n",
    var_dump($app->request()), "\n\n",
    'Arguments can be retrieved like this:', "\n\n",
    var_dump($args),
    '</pre>';
});

// // @use /sample or /sample/100
$app->route('GET /sample(/\d+)?', function () use ($app) {
  echo
    '<pre >',
    'Arguments can be retrieved like this:', "\n\n",
    var_dump($app->request()),
    '</pre>';
});

// @use /welcome
$app->route('GET /xml', function () use ($app) {
  $app->response(200, ['message' => 'You are very welcome!', 'output' => 'xml'], 'xml');
});

// @use /welcome
$app->route('GET /welcome', function () use ($app) {
  $app->response(200, ['message' => 'You are very welcome!', 'data' => ['foo' => 'bar']]);
});

// @use /
$app->route('GET /', function () use ($app) {
  header("location: {$app->request()->base}/welcome", 302);
});

$app->run();
