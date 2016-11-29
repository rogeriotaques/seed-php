<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/php-api-kit
 * -------------------------------------------------------- */

defined('ENV') or die('Direct script access is not allowed!');

$cfg = []; // DO NOT REMOVE THIS LINE 

/* --------------------------------------------------------
 | Current server address  
 * -------------------------------------------------------- */
$cfg['base-url'] = [
  'production'  => "{$_SERVER['HTTP_HOST']}/",
  'development' => "localhost/"
];

/* --------------------------------------------------------
 | Allowed methods  
 * -------------------------------------------------------- */
$cfg['cors'] = true;
$cfg['methods'] = [ 'get', 'post', 'put', 'delete', 'options', 'patch' ];

/* --------------------------------------------------------
 | Allowed headers keys 
 * -------------------------------------------------------- */
$cfg['headers'] = [ 'Origin', 'Content-Type', 'X-Auth-Token' , 'Authorization', 'X-Requested-With' ];

/* --------------------------------------------------------
 | Cache control  
 * -------------------------------------------------------- */
$cfg['cache'] = true;
$cfg['cache-max-age'] = 3600;

/* --------------------------------------------------------
 | Database
 * -------------------------------------------------------- */
$cfg['database'] = [
  'development' => [
    'host' => 'localhost',
    'port' => '3306',
    'user' => 'root',
    'pass' => '',
    'base' => 'php-api-kit',
    'charset' => 'utf8'
  ],
  'production' => [
    'host' => 'localhost',
    'port' => '3306',
    'user' => 'root',
    'pass' => '',
    'base' => 'php-api-kit',
    'charset' => 'utf8'
  ]
];

/* --------------------------------------------------------
 | Usage log 
 * -------------------------------------------------------- */
$cfg['log'] = false;

/* --------------------------------------------------------
 | Other 
 * -------------------------------------------------------- */
$cfg['language'] = 'en';
$cfg['charset'] = 'utf8';
