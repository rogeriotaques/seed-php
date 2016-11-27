<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 |
 | Special routes that are gonna be used by Seed PHP to 
 | load the right controller and method according to given URI.
 |
 | e.g:
 | $routes['your/special/route'] = 'existing/route';
 | $routes['(regular|expression)/is/allowed'] = 'existing/route/$1';
 * -------------------------------------------------------- */

$routes = []; // PLEASE DO NOT REMOVE THIS LINE 

$routes['status'] = 'master/status';
$routes['(\w+)(/.*){0,1}'] = 'resources$2';
