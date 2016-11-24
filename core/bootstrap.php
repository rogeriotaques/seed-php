<?php 
 /* --------------------------------------------------------
 | Seed PHP - Bootstrap
 |
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 | 
 | It's responsible to register classes autoloaders and 
 | to include specifi files required by Seed PHP.
 * -------------------------------------------------------- */

defined('ENV') or die('Direct script access is not allowed!');

$sys_path = ['config', 'controllers', 'libraries'];

foreach ($sys_path as $path) { 
  ini_set(
    'include_path', 
    get_include_path() . PATH_SEPARATOR . dirname(__DIR__) . DIRECTORY_SEPARATOR . $path
  ); 
};

// register an autoload for classes
spl_autoload_register (
  function ( $class ) {
    $class = str_replace(["_"],"\\", $class);
    $class = ltrim($class, '\\');
    $file  = '';
    $namespace = '';

    if ($lastNsPos = strripos($class, '\\')) {
        $namespace = substr($class, 0, $lastNsPos);
        $class = substr($class, $lastNsPos + 1);
        $file  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $file .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

    // load files in lowercase and named with dashes when class names are camelcase
    if ( !file_exists( strtolower($file) ) ) {
      $file  = preg_replace('/(\w)([ABCDEFGHIJKLMNOPQRSTUVWXYZ]{1})(.*)$/', '$1-$2$3', $file);
      $file  = strtolower($file);

      if ( !file_exists($file) ) {
        throw new Exception("Autoloader: Impossible to find the class '{$class}'");
      }
    } 

    // force lower case paths ...
    $file = strtolower($file);
    
    if (file_exists($file) && !@include_once($file)) {
      throw new Exception("Autoloader: Impossible to load class '{$class}'");
    }

    // echo "Loaded file: {$file}<br >"; 
  }
);

// include all required files

$system_files = [
  'config/settings.php',  // basic settings
  'config/routes.php'     // special routes for the application  
];

foreach ($system_files as $sf) {
  // does file exist? 
  if (file_exists($sf)) {
    require_once( $sf );
  } else {
    die("Missing system file '{$sf}'. <br >Aborted.");
  }
}

