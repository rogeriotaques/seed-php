<?php 

 /* --------------------------------------------------------
 | Seed PHP - Settings File
 |
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 | 
 | This file should be used to define all libraries and 
 | other resources settings files. You can also create 
 | specific files under 'config' folder for each lib.
 * -------------------------------------------------------- */

defined('ENV') or die('Direct script access is not allowed!');

$cfg = [];

/* --------------------------------------------------------
 | Current server base URL 
 * -------------------------------------------------------- */
$cfg['base-url'] = [
  'production'  => "{$_SERVER['HTTP_HOST']}/",
  'development' => "localhost/fakeapi/"
];

/* --------------------------------------------------------
 | Allowed headers 
 * -------------------------------------------------------- */
$cfg['headers'] = [];

/* --------------------------------------------------------
 | Endpoints for third party APIs. 
 * -------------------------------------------------------- */
$cfg['endpoints'] = [];

/* --------------------------------------------------------
 | Logger
 * -------------------------------------------------------- */

$cfg['logger'] = [
  'development' => [
    'host' => 'localhost',
    'port' => '3306',
    'user' => 'root',
    'pass' => '',
    'base' => 'fakeapi',
    'table' => 'log_usage'
  ],
  'production' => [
    'host' => 'localhost',
    'port' => '3306',
    'user' => 'abtz',
    'pass' => 'abt11235',
    'base' => 'fakeapi',
    'table' => 'log_usage'
  ]
];
