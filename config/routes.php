<?php 

 /* --------------------------------------------------------
 | Seed PHP - Routes File
 |
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 |
 | Special routes that are gonna be used by Seed PHP to 
 | load the right controller and method according to given URI.
 |
 | E.g:
 | $routes['your/special/route'] = 'existing/route';
 | $routes['(regular|expression)/is/allowed'] = 'existing/route/$1';
 * -------------------------------------------------------- */

$routes = []; // PLEASE DO NOT REMOVE THIS LINE 

$routes['figures'] = 'master/figures';
$routes['(\w+)(/.*){0,1}'] = 'resources$2';
