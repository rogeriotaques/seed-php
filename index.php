<?php

/**
 * Seed-PHP Microframework.
 * @author Rogerio Taques
 * @license MIT
 * @see http://github.com/rogeriotaques/seed-php
 */
 
/**
 * Forces the API tomezone.
 */
date_default_timezone_set('Asia/Tokyo');

/**
 * Requires composer auto-loader
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Imports the SeedPHP stand-alone loader.
 */
if (!require_once('loader.php')) {
    require_once __DIR__ . '/loader.php';
}

use SeedPHP\Helper\Http;

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

/**
 * Gets the SeedPHP instance.
 */
$app = SeedPHP\App::getInstance();

/** @var array - Settings for the RateLimit Helper */
$rateLimitSettings = [ 'limit_per_hour' => 20, 'seconds_delay' => 2, 'seconds_banned' => 2 ];

/**
 * Load the RateLimit helper attaching it to the App instance.
 * This is optional.
 */
$app->load('ratelimit', $rateLimitSettings);

/**
 * Forces the page to turn off caching.
 * This is optional.
 */
$app->setCache(false);

/**
 * Sets the error handler. 
 * This is optional.
 */
$app->onFail(function ($error) {
    echo '<h1>Oops! An error has happened! </h1>';
    echo "<p >The error sais: {$error->code} - {$error->message}</p>";

    echo
        '<pre >',
        'This is what we give to your error handler:', "\n\n",
        var_dump($error),
        '</pre>';

    return;
});

/**
 * Print the execution of a ratelimit.
 * @use /ratelimit
 */
$app->route('GET /ratelimit', function () use ($app, $rateLimitSettings) {
    $app->ratelimit->test();

    echo "You may try refreshing this page repeated times to see the limits increasing and eventually the call getting denied. <br ><br >";
    
    echo "Current options in use are:";
    echo "<pre >", print_r($rateLimitSettings, true), "</pre>", "<br >";
    
    echo "After calling 16 times within an hour (be careful to not call more than twice per second), you will see the throttling taking place and your request will be delayed by 2 seconds.<br >";
    echo "Aways you get banned, the ban time (retry-after) will double its current value until get reset.";

    echo "<br ><br >";
    
    echo "Your current status is:";
    echo "<pre >", print_r($_SESSION['rate-limit'][Http::getClientIP()], true), "</pre>", "<br >";

    return;
});

/**
 * Print the execution of a database.
 * @use /database
 */
$app->route('GET /database', function () use ($app) {
    // NOTICE:
    // THIS HELPER HAS BEEN DEPRECATED
    // $app->load(
    //     'mysql',
    //     [
    //         'host' => 'localhost',
    //         'port' => '3306',
    //         'user' => 'root',
    //         'pass' => '',
    //         'base' => 'issuer'
    //     ]
    // );
    // run_test_query();
    // echo '<br >';

    $app->load(
        'database',
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

    return;
});

/**
 * Print received arguments based on the called path.
 * Note that the order mathers and longer patters from the same URL should be given last.
 * @use /sample/some-verb OR
 * @use /sample/some-verb/123 
 */
$app->route('GET /sample(/\d+)?', function () use ($app) {
    echo
        '<pre >',
        'Arguments can be retrieved like this:', "\n\n",
        print_r($app->request(), true),
        '</pre>';

    return;
});

/**
 * Print received arguments based on the called path.
 * Note that the order mathers and longer patters from the same URL should be given last.
 * @use /sample/some-verb OR
 * @use /sample/some-verb/some-id OR 
 * @use /sample/some-verb/123 OR
 * @use /sample/some-verb/123/arg1 OR
 * @use /sample/some-verb/123/arg1/foo OR
 * @use /sample/some-verb/123/arg2/bar ...
 */
$app->route('GET /sample(/[a-z0-9\-]+)?(/[a-z0-9\-]+)?(/[a-z0-9\-]+){0,}', function ($args) use ($app) {
    echo
        '<pre >',
        'Arguments can be retrieved like this:', "\n\n",
        print_r($app->request(), true), "\n\n",
        'Arguments can be retrieved like this:', "\n\n",
        print_r($args, true),
        '</pre>';

    return;
});


/**
 * Returns a XML document
 * @use /xml
 */
$app->route('GET /xml', function () use ($app) {
    return $app->response(200, ['message' => 'You are very welcome!', 'output' => 'xml'], 'xml');
});

/**
 * Render the welcome page
 * @use /welcome
 */
$app->route('GET /welcome', function () use ($app) {
    return $app->response(200, ['message' => 'You are very welcome!', 'data' => ['foo' => 'bar']]);
});

/**
 * Parse and print (twig) template given email body .
 * @use /mailgun-twig
 */
$app->route('GET /mailgun-twig', function () use ($app) {
    $app->load('mailgun', ['apiKey' => 'abc', 'domain' => 'def']);

    echo $app->mailgun->parse(
        __DIR__ . '/test/test.twig',
        [
            'title' => 'Hello World',
            'description' => 'This is a page generated by Twig engine.',
            'list' => ['One', 'Two', 'Three'],
            'test' => false
        ],
        'twig'
    );

    return;
});

/**
 * Parse and print string given email body .
 * @use /mailgun-twig-str
 */
$app->route('GET /mailgun-twig-string', function () use ($app) {
    $app->load('mailgun', ['apiKey' => 'abc', 'domain' => 'def']);

    echo $app->mailgun->parse(
        '<h1 >{{ title }}</h1><p >{{ description }}</p>',
        [
            'title' => 'Hello World',
            'description' => 'This is a page generated by Twig engine.',
        ],
        'twig',
        'string'
    );

    return;
});

/**
 * Redirects to another page.
 * @use /
 */
$app->route('GET /', function () use ($app) {
    header("location: {$app->request()->base}/welcome", 302);
    return;
});

$app->run();
