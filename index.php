<?php

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 * -------------------------------------------------------- */
 
// define the api timezone 
date_default_timezone_set('Asia/Tokyo');

// set error reporting 
error_reporting(E_ALL & ~E_NOTICE);

// let's define the environment 
// it can be whatever you want. usually will be either 'development' or 'production'
if (!defined('ENV')) {
  define('ENV', getenv('ENV') !== false ? getenv('ENV') : 'development');
}

// import loader ...  
if (!require_once('seed/loader.php')) {
  die("Loader not found! Aborted.");
}

use Seed\Router;

// retrieve requested URI 
$uri  = isset($_GET['uri']) ? $_GET['uri'] : '';

// initialise the router 
$router = new Router( $uri );

// let's rock ...
$router->run( $uri );
