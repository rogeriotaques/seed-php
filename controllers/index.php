<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 * -------------------------------------------------------- */

namespace Controllers;

defined('ENV') or die('Direct script access is not allowed!');

use Seed\Controller;

class Index extends Controller {

  function __construct () {
    parent::__construct();
  }

  public function index_get () {
    header('location: welcome/', 302);
  }
} // class
