<?php

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 * -------------------------------------------------------- */

namespace Seed;

defined('ENV') or die('Direct script access is not allowed!');

use Seed\Nucleos;
use Seed\Router;

class Controller extends Nucleos {
  protected $request;

  function __construct () {
    parent::__construct();
    $this->request = new Router();
  }

} // class
