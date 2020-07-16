<?php

/**
 * Seed-PHP Microframework
 * @author Rogerio Taques
 * @license MIT
 * @see http://github.com/rogeriotaques/seed-php
 */

if (!function_exists('seed_loader')) {
    function seed_loader($class)
    {
        $base_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        // Support classes grouped in directories and named such as "Controller_Myclass",
        // where "Controller" will be a directory and "Myclass" the class file name.
        $class = str_replace(["_"], "\\", $class);
        $class = ltrim($class, '\\');

        $file = '';
        $namespace = '';
        $expected_namespace = 'SeedPHP';

        if ($lastNsPos = strripos($class, '\\')) {
            $namespace = substr($class, 0, $lastNsPos);
            
            // Do not try to load the classes from SeedPHP when using a different namespace
            // @since 1.1.6
            if (strpos($namespace, $expected_namespace) === false) {
                return;
            }
            
            $class = substr($class, $lastNsPos + 1);
        }
        
        // Converts NameSpace like into name-space 
        // @since 1.9.1
        $namespace = strtolower( preg_replace('/(\w)([ABCDEFGHIJKLMNOPQRSTUVWXYZ]{1})(.*)$/', '$1-$2$3', $namespace) );

        // Prepare the file name
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

        // Does the file exist?
        if (!file_exists($base_dir . $file)) {
            // Try to find a Camel-Case like file.
            $file = preg_replace('/(\w)([ABCDEFGHIJKLMNOPQRSTUVWXYZ]{1})(.*)$/', '$1-$2$3', $file);

            if (!file_exists($base_dir . $file)) {
                // Not lucky! Last trial, now all lowercase ... 
                $file = strtolower($file);

                // But, if even after considering a dashed named file,
                // the file is not found, throw an eception ...
                if (!file_exists($base_dir . $file)) {
                    throw new Exception("SeedPHP Autoloader: Impossible to find the class file '{$class}'");
                } // not lower-case file
            } // not Snake-Case file
        } // not CamelCase file

        // Does the file exist? Can we include it?
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
