<?php

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/php-api-kit
 * -------------------------------------------------------- */

namespace Seed;

defined('ENV') or die('Direct script access is not allowed!');

class Nucleos {
  private $_version = '1.0.2';
  protected $config;

  function __construct () {
    if (!@include('config/settings.php')) {
      die("Missing the essential file: 'config/settings.php'. Aborted.");
    }

    $this->config = $cfg;
    unset($cfg);
  }

  public function getConfig () {
    return $this->config;
  }

  public function getVersion() {
    return $this->_version;
  }
} // class 
