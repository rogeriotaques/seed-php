<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 * -------------------------------------------------------- */

defined('ENV') or die('Direct script access is not allowed!');

$cfg = []; // DO NOT REMOVE THIS LINE 

/* --------------------------------------------------------
 | Current server address  
 * -------------------------------------------------------- */
$cfg['base-url'] = [
  'production'  => "{$_SERVER['HTTP_HOST']}/",
  'development' => "localhost/fakeapi/"
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
    'base' => 'fakeapi',
    'table' => 'log_usage',
    'max-records-per-page' => 20,
    'charset' => 'utf8'
  ],
  'production' => [
    'host' => 'localhost',
    'port' => '3306',
    'user' => 'abtz',
    'pass' => 'abt11235',
    'base' => 'fakeapi',
    'table' => 'log_usage',
    'max-records-per-page' => 20,
    'charset' => 'utf8'
  ]
];

/* --------------------------------------------------------
 | Usage log 
 * -------------------------------------------------------- */
$cfg['log'] = true;

/* --------------------------------------------------------
 | Other 
 * -------------------------------------------------------- */
$cfg['language'] = 'en';
$cfg['charset'] = 'utf8';
