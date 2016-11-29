<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/php-api-kit
 * -------------------------------------------------------- */

defined('ENV') or die('Direct script access is not allowed!');

// essential paths in the kit 
$essential_paths = ['config', 'controllers', 'models', 'seed', 'seed/libraries'];

// let's make sure that essential paths are in the include path 
foreach ($essential_paths as $path) { 
  ini_set(
    'include_path', 
    get_include_path() . PATH_SEPARATOR . dirname(__DIR__) . DIRECTORY_SEPARATOR . $path
  ); 
};

// ----------------------------
// autoloader 
// ----------------------------
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

    // load files in lowercase and named 
    // with dashes when class names are camelcase
    if ( !file_exists( strtolower($file) ) ) {
      $file = preg_replace('/(\w)([ABCDEFGHIJKLMNOPQRSTUVWXYZ]{1})(.*)$/', '$1-$2$3', $file);
      $file = strtolower($file);

      // even after consider the dashed named files 
      // file is not found, throw an eception ...
      if ( !file_exists($file) ) {
        throw new Exception("Autoloader: Impossible to find the class '{$class}'");
      }
    } 

    // force lower case paths ...
    // (makes all difference on Linux)
    $file = strtolower($file);
    
    // does the file exist? can we include it?
    if (file_exists($file) && !@include_once($file)) {
      throw new Exception("Autoloader: Impossible to load class '{$class}'");
    }

    // echo "Loaded file: {$file}<br >"; 
  }
);
