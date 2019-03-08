<?php

/** 
 * Seed-PHP Microframework
 * @copyright Abtz Labs
 * @license MIT
 * @see http://github.com/abtzco/seed-php
 */

if (!function_exists('seed_loader')) {
  function seed_loader($class)
  {
    $base_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

    $class = str_replace(["_"], "\\", $class);
    $class = ltrim($class, '\\');

    $file = '';
    $namespace = '';

    if ($lastNsPos = strripos($class, '\\')) {
      $namespace = substr($class, 0, $lastNsPos);

      // backward compatbility for previous namespace
      if (strpos($namespace, 'SeedPHP') === false) {
        $namespace = 'SeedPHP' . str_replace('Seed', '', $namespace);
      }

      $class = substr($class, $lastNsPos + 1);
      $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $file .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

    // does the file exist?
    if (!file_exists($base_dir . $file)) {
      // converts CamelCase files names into camel-case.
      $file = preg_replace('/(\w)([ABCDEFGHIJKLMNOPQRSTUVWXYZ]{1})(.*)$/', '$1-$2$3', $file);
      $file = strtolower($file);

      // even after consider the dashed named files
      // file is not found, throw an eception ...
      if (!file_exists($base_dir . $file)) {
        throw new Exception("SeedPHP Autoloader: Impossible to find the class file '{$class}'");
      }
    }

    // does the file exist? can we include it?
    if (file_exists($base_dir . $file) && !@include_once($base_dir . $file)) {
      throw new Exception("SeedPHP Autoloader: Impossible to load class '{$class}'");
    }

    // echo "Loaded file: {$base_dir}{$file}<br >";
  }
}

// ----------------------------
// autoloader
// ----------------------------
spl_autoload_register('seed_loader');
