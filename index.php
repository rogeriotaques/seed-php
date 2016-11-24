<?php
 /* --------------------------------------------------------
 | Seed PHP
 |
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 * -------------------------------------------------------- */
 
namespace Root;
use Core\RestRouter;

date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL & ~E_NOTICE);

if (!defined('ENV')) {
  define('ENV', getenv('ENV') !== false ? getenv('ENV') : 'development');
}

// import setting file 
if (file_exists('core/bootstrap.php') ) {
  require_once('core/bootstrap.php');
} else {
  die("Bootstrap file was not found! <br >Aborted.");
}

$router = new RestRouter();
$call   = isset($_GET['uri']) ? $_GET['uri'] : '';
$cache_max_age = 3600;

// headers
header("Access-Control-Allow-Origin: *");
header('Content-language: en');
header('Cache-Control: max-age=' . $cache_max_age);
header('Expires: '.gmdate('D, d M Y H:i:s', time() + $cache_max_age ).' GMT');

$router->run( $call, function () {
  header('location: welcome/', true, 302);
} );