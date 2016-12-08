<?php 

/**
 * Uses http://simpletest.org for unit tests. 
 * @use $ php tests/main.php 
 */

if ( !require(dirname(__FILE__) . '/../seed/loader.php') ) {
  die('Seed-PHP autoload not found! Aborted.');
}

if ( !require(dirname(__FILE__) . '/../vendor/simpletest/simpletest/autorun.php') ) {
  die('SimpleTest autoload not found! Aborted.');
}

include dirname(__FILE__) . '/unit-test.php';
