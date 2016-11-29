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

use Seed\Nucleos;
use Seed\Libraries\RMySQL;

class Model extends Nucleos {
  protected $db;

  function __construct () {
    parent::__construct();
    $this->db = new RMySQL($this->config['database'][ENV]);
  }
  
} // class
