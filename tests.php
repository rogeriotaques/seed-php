<?php

/**
 * Seed-PHP Test Suite
 * Run: php tests.php
 * Vanilla PHP. No external test framework. Unit tests run in-process; HTTP tests
 * spin up a temporary server using tests/app.php as the router.
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '1');

require_once __DIR__ . '/loader.php';

$TEST_PORT = 8090;
$TEST_HOST = 'localhost';
$BASE = "http://{$TEST_HOST}:{$TEST_PORT}";
$SERVER_LOG = sys_get_temp_dir() . '/seedphp_test_server.log';

$passed = 0;
$failed = 0;
$skipped = 0;
$serverProc = null;
$pipes = [];

function colorGreen(string $s): string { return "\033[32m" . $s . "\033[0m"; }
function colorRed(string $s): string { return "\033[31m" . $s . "\033[0m"; }
function colorYellow(string $s): string { return "\033[33m" . $s . "\033[0m"; }
function colorBold(string $s): string { return "\033[1m" . $s . "\033[0m"; }

function group(string $name): void
{
    echo "\n" . colorBold('### ' . $name . ' ###') . "\n";
}

function pass(string $msg): void
{
    global $passed;
    $passed++;
    echo '  ' . colorGreen('PASS') . ' ' . $msg . "\n";
}

function fail(string $msg, $expected = null, $actual = null): void
{
    global $failed;
    $failed++;
    echo '  ' . colorRed('FAIL') . ' ' . $msg . "\n";

    if (func_num_args() > 1) {
        echo '    ' . colorRed('expected: ' . var_export($expected, true)) . "\n";
        echo '    ' . colorRed('actual:   ' . var_export($actual, true)) . "\n";
    }
}

function skip(string $msg): void
{
    global $skipped;
    $skipped++;
    echo '  ' . colorYellow('SKIP') . ' ' . $msg . "\n";
}

function assert_true($value, string $msg): void
{
    if ($value === true) {
        pass($msg);
        return;
    }

    fail($msg, true, $value);
}

function assert_eq($expected, $actual, string $msg): void
{
    if ($expected === $actual) {
        pass($msg);
        return;
    }

    fail($msg, $expected, $actual);
}

function assert_contains(string $needle, string $haystack, string $msg): void
{
    if (strpos($haystack, $needle) !== false) {
        pass($msg);
        return;
    }

    fail($msg, "contains: {$needle}", $haystack);
}

function assert_throws(string $class, callable $fn, string $msg): void
{
    try {
        $fn();
    } catch (\Throwable $e) {
        if ($e instanceof $class) {
            pass($msg);
            return;
        }

        fail($msg, $class, get_class($e));
        return;
    }

    fail($msg, $class, 'no exception thrown');
}

function http_request(string $method, string $path, ?array $body = null, ?string $cookie = null): array
{
    global $BASE;

    $ch = curl_init();
    $options = [
        CURLOPT_URL => $BASE . $path,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => $method,
    ];

    if ($cookie !== null) {
        $options[CURLOPT_COOKIEFILE] = $cookie;
        $options[CURLOPT_COOKIEJAR] = $cookie;
    }

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($body);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr((string) $response, 0, $headerSize);
    $rawBody = substr((string) $response, $headerSize);
    curl_close($ch);

    return [
        'status' => $status,
        'headers' => $headers,
        'body' => $rawBody,
        'json' => json_decode($rawBody, true),
    ];
}

function start_server(string $host, int $port, string $logFile): array
{
    $command = sprintf(
        'php -S %s:%d %s',
        $host,
        $port,
        escapeshellarg(__DIR__ . '/tests/app.php')
    );

    $descriptors = [
        ['pipe', 'r'],
        ['file', $logFile, 'a'],
        ['file', $logFile, 'a'],
    ];

    $proc = proc_open($command, $descriptors, $pipes);

    if (!is_resource($proc)) {
        return [null, []];
    }

    for ($i = 0; $i < 50; $i++) {
        $sock = @fsockopen($host, $port, $errno, $errstr, 0.1);

        if ($sock) {
            fclose($sock);
            return [$proc, $pipes];
        }

        usleep(100000);
    }

    return [$proc, $pipes];
}

echo colorBold('Seed-PHP Test Suite') . "\n";
echo str_repeat('=', 80) . "\n";

group('Autoloader');

assert_true(class_exists('SeedPHP\\App'), 'loads SeedPHP\\App');
assert_true(class_exists('SeedPHP\\Core'), 'loads SeedPHP\\Core');
assert_true(class_exists('SeedPHP\\Router'), 'loads SeedPHP\\Router');
assert_true(class_exists('SeedPHP\\Helper\\Http'), 'loads SeedPHP\\Helper\\Http');
assert_true(class_exists('SeedPHP\\Helper\\Database'), 'loads SeedPHP\\Helper\\Database');
assert_true(class_exists('SeedPHP\\Helper\\Curl'), 'loads SeedPHP\\Helper\\Curl');
assert_true(class_exists('SeedPHP\\Helper\\RateLimit'), 'loads SeedPHP\\Helper\\RateLimit');
assert_true(class_exists('SeedPHP\\Helper\\Logger'), 'loads SeedPHP\\Helper\\Logger');
assert_true(class_exists('SeedPHP\\Helper\\Mysql'), 'loads SeedPHP\\Helper\\Mysql');

assert_throws(Exception::class, function () {
    class_exists('SeedPHP\\ThisDoesNotExist');
}, 'throws for a missing class');

group('App singleton');

$app = SeedPHP\App::getInstance();

assert_true($app === SeedPHP\App::getInstance(), 'getInstance() returns the same instance');
assert_true($app instanceof SeedPHP\Core, 'getInstance() returns the Core engine');
assert_true(method_exists($app, 'route'), 'exposes route()');
assert_true(method_exists($app, 'run'), 'exposes run()');
assert_true(method_exists($app, 'load'), 'exposes load()');

group('Http helper');

$status = SeedPHP\Helper\Http::getHTTPStatus(200);
assert_eq(200, $status['code'], 'getHTTPStatus(200) code');
assert_eq('OK', $status['message'], 'getHTTPStatus(200) message');

$status = SeedPHP\Helper\Http::getHTTPStatus(404);
assert_eq('Not Found', $status['message'], 'getHTTPStatus(404) message');

assert_throws(ErrorException::class, function () {
    SeedPHP\Helper\Http::getHTTPStatus(599);
}, 'getHTTPStatus() throws on an unknown code');

$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SERVER_PORT'] = 80;
unset($_SERVER['HTTPS']);
assert_eq('http://localhost', SeedPHP\Helper\Http::getBaseUrl(), 'getBaseUrl() detects http');

$_SERVER['HTTPS'] = 'on';
assert_eq('https://localhost', SeedPHP\Helper\Http::getBaseUrl(), 'getBaseUrl() detects https');

$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
unset(
    $_SERVER['HTTP_CLIENT_IP'],
    $_SERVER['HTTP_CF_CONNECTING_IP'],
    $_SERVER['HTTP_X_FORWARDED'],
    $_SERVER['HTTP_X_FORWARDED_FOR'],
    $_SERVER['HTTP_FORWARDED'],
    $_SERVER['HTTP_FORWARDED_FOR']
);
assert_eq('203.0.113.9', SeedPHP\Helper\Http::getClientIP(), 'getClientIP() falls back to REMOTE_ADDR');

group('Router response');

assert_true($app->route('GET /unit-test', function () {}) === $app, 'route() is chainable');

ob_start();
$app->response(200, ['foo' => 'bar']);
$out = ob_get_clean();
assert_contains('"status":200', $out, 'response() json status');
assert_contains('"message":"OK"', $out, 'response() json message');
assert_contains('"foo":"bar"', $out, 'response() json payload');

ob_start();
$app->response(404, ['message' => 'nope']);
$out = ob_get_clean();
assert_contains('"error":true', $out, 'response() flags errors');

ob_start();
$app->response(200, ['message' => 'ok'], 'xml');
$out = ob_get_clean();
assert_contains('<status>200</status>', $out, 'response() xml status');
assert_contains('<message>ok</message>', $out, 'response() xml message');

ob_start();
$returned = $app->response(200, ['a' => 1], false);
$out = ob_get_clean();
assert_eq('', $out, 'response() with output=false does not echo');
assert_true(is_array($returned), 'response() with output=false returns the array');

$_GET['_router_status'] = 'code';
ob_start();
$app->response(200, ['a' => 1]);
$out = ob_get_clean();
unset($_GET['_router_status']);
assert_contains('"code":200', $out, 'response() honours _router_status');

group('Database helper (sqlite)');

$db = new SeedPHP\Helper\Database(['driver' => 'sqlite']);
$db->setDNS('sqlite::memory:');

assert_true($db->connect() === $db, 'connect() is chainable');
assert_true($db->getLink() instanceof PDO, 'getLink() returns a PDO');
assert_eq(1, $db->exec('create table items (id integer primary key autoincrement, name text)'), 'exec() returns 1 for DDL');
assert_eq(1, $db->insert('items', ['name' => 'alpha']), 'insert() returns 1');
$db->insert('items', ['name' => 'beta']);
assert_eq(2, $db->insertedId(), 'insertedId() returns the last id');

$rows = $db->fetch('items', ['*'], [], 10, 0, ['id' => 'ASC']);
assert_eq(2, count($rows), 'fetch() returns both rows');
assert_eq('alpha', $rows[0]['name'], 'fetch() keeps insertion order');

$bound = $db->exec('select * from items where id = :id', ['id' => 1]);
assert_eq('alpha', $bound[0]['name'], 'exec() binds named parameters');

$db->update('items', ['name' => 'gamma'], ['id' => 1]);
$rows = $db->fetch('items', ['*'], ['id' => 1]);
assert_eq('gamma', $rows[0]['name'], 'update() changes a row');

$db->delete('items', ['id' => 2]);
assert_eq(1, count($db->fetch('items')), 'delete() removes a row');
assert_eq(0, $db->resultCount(), 'resultCount() is 0 after a non-select');

$db->transaction('begin');
$db->insert('items', ['name' => 'delta']);
$db->transaction('commit');
assert_eq(2, count($db->fetch('items')), 'transaction commit persists');

$db->disconnect();
assert_throws(ErrorException::class, function () {
    (new SeedPHP\Helper\Database())->exec('select 1');
}, 'exec() without a connection throws');

group('Curl helper');

$curl = new SeedPHP\Helper\Curl();
assert_true($curl->create('http://localhost') === $curl, 'create() is chainable');
assert_true($curl->option(CURLOPT_TIMEOUT, 5) === $curl, 'option() is chainable');
assert_true($curl->data(['a' => 1]) === $curl, 'data() is chainable');
skip('Curl::execute() performs a network request');

group('Logger helper');

$logger = new SeedPHP\Helper\Logger(['driver' => 'sqlite', 'base' => ':memory:']);
assert_true($logger instanceof SeedPHP\Helper\Logger, 'instantiates with config');
skip('Logger::log() needs a live database connection');

group('Mysql helper');

assert_throws(ErrorException::class, function () {
    new SeedPHP\Helper\Mysql();
}, 'deprecated constructor throws');

group('Mailgun helper');

$mailgun = new SeedPHP\Helper\Mailgun(['apiKey' => 'key', 'domain' => 'example.test']);
assert_true($mailgun instanceof SeedPHP\Helper\Mailgun, 'instantiates with config');
if (class_exists('Twig\\Environment')) {
    skip('Mailgun::parse() template rendering');
} else {
    skip('Mailgun::parse() needs Twig (vendor not installed)');
}
skip('Mailgun::send() performs a network request');

group('HTTP routing (live server)');

$portBusy = @fsockopen($TEST_HOST, $TEST_PORT, $errno, $errstr, 0.2);
if ($portBusy) {
    fclose($portBusy);
    fail("port {$TEST_PORT} is already in use");
} else {
    @unlink($SERVER_LOG);
    list($serverProc, $pipes) = start_server($TEST_HOST, $TEST_PORT, $SERVER_LOG);

    register_shutdown_function(function () use (&$serverProc) {
        if (is_resource($serverProc)) {
            proc_terminate($serverProc);
            proc_close($serverProc);
        }
    });

    $ready = @fsockopen($TEST_HOST, $TEST_PORT, $errno, $errstr, 0.2);
    if ($ready) {
        fclose($ready);
    } else {
        fail("test server did not start on port {$TEST_PORT}");
    }

    if ($ready) {
        $r = http_request('GET', '/');
        assert_eq(200, $r['status'], 'GET / returns 200');
        assert_eq('root', $r['body'], 'GET / echoes the handler output');

        $r = http_request('GET', '/json');
        assert_eq(200, $r['status'], 'GET /json returns 200');
        assert_eq(200, $r['json']['status'], 'GET /json status field');
        assert_eq('bar', $r['json']['data']['foo'], 'GET /json data payload');

        $r = http_request('GET', '/xml');
        assert_contains('<response>', $r['body'], 'GET /xml returns XML');
        assert_contains('<status>200</status>', $r['body'], 'GET /xml status node');

        $r = http_request('GET', '/error');
        assert_eq(404, $r['status'], 'GET /error returns 404');
        assert_true($r['json']['error'] === true, 'GET /error flags the error');

        $r = http_request('GET', '/hooks');
        assert_eq('before|main|after', $r['body'], 'before and after hooks run in order');

        $r = http_request('GET', '/sample/123');
        assert_eq('123', $r['json']['id'], 'numeric path segment becomes the id');

        $r = http_request('POST', '/methods');
        assert_eq('POST', $r['json']['method'], 'POST method is routed');

        $r = http_request('GET', '/methods');
        assert_eq('GET', $r['json']['method'], 'GET method is routed');

        $r = http_request('OPTIONS', '/json');
        assert_eq(200, $r['status'], 'OPTIONS is accepted');

        $r = http_request('GET', '/nope');
        assert_eq(501, $r['status'], 'unknown route returns 501');

        $cookie = tempnam(sys_get_temp_dir(), 'seedphp_cookie_');
        $r = http_request('GET', '/ratelimit', null, $cookie);
        assert_eq(200, $r['status'], 'ratelimit allows the first call');

        $banned = false;
        for ($i = 0; $i < 6; $i++) {
            $r = http_request('GET', '/ratelimit', null, $cookie);
            if ($r['status'] === 429) {
                $banned = true;
                break;
            }
        }
        assert_true($banned, 'ratelimit bans after repeated calls');
        @unlink($cookie);
    }

    if (is_resource($serverProc)) {
        proc_terminate($serverProc);
        proc_close($serverProc);
        $serverProc = null;
    }
}

echo "\n" . colorBold(str_repeat('=', 80)) . "\n";
$total = $passed + $failed + $skipped;
$summary = sprintf('%d PASSED. %d FAILED. %d SKIP.', $passed, $failed, $skipped);
echo ($failed === 0 ? colorGreen($summary) : colorRed($summary)) . "\n";
echo colorBold(sprintf('TOTAL: %d TEST CASES.', $total)) . "\n";
echo colorBold(str_repeat('=', 80)) . "\n";

exit($failed > 0 ? 1 : 0);
