<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework.
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.6.0
 | @license MIT
 | @see http://github.com/abtzco/seed-php
 * -------------------------------------------------------- */

namespace Seed;

defined('SEED') or die('Direct script access is not allowed!');

use Seed\Helper\Http;

class Core extends \Seed\Router {

  private static $instance;

  // an object containing the request data
  private $_request;

  function __construct () {}

  // ~~~ PUBLIC METHODS ~~~

  public static function getInstance () {
    if (is_null(self::$instance)) {
      self::$instance = new Core();
    }

    return self::$instance;
  } // getInstance

  public function run () {

    $this->setPageHeaders();
    $this->buildRequest();

    // is it an OPTIONS request?
    // it's used to confirm if app accept CORS calls
    if ($this->_method === 'OPTIONS') {
      return $this->response(Http::_OK); // do accept it
    }

    return $this->dispatch( $this->_request->args );
  } // run

  public function header ( $key = null ) {
    $headers = getallheaders();

    if ($key === null) {
      return $headers;
    }

    return ( isset($headers[$key]) ? $headers[$key] : false );
  } // header

  public function post ( $key = null ) {
    // The 'always_populate_raw_post_data' is deprecated since php@5.6.
    // So, check if auto_populate_post_data is enabled, if not enabled, use php://input instead.
    $post_data = [];

    if (intval( ini_get('always_populate_raw_post_data') ) < 1) {
      $input = file_get_contents("php://input");

      if (is_null($post_data = json_decode($input, true))) {
        // Sometimes input cannot be decoded from json, then try to parse it from string.
        parse_str(file_get_contents("php://input"), $post_data);
      } 
    } else {
      $post_data = $_POST;
    }

    if ($key === null) {
      return $post_data;
    }

    return ( isset($post_data[$key]) ? $post_data[$key] : false );
  } // post

  public function get ( $key = null ) {
    if ($key === null) {
      return $_GET;
    }

    return ( isset($_GET[$key]) ? $_GET[$key] : false );
  } // get

  public function file ( $key = null ) {
    if ($key === null) {
      return $_FILES;
    }

    return ( isset($_FILES[$key]) ? $_FILES[$key] : false );
  } // file

  public function cookie ( $key = null ) {
    if ($key === null) {
      return $_COOKIE;
    }

    return ( isset($_COOKIE[$key]) ? $_COOKIE[$key] : false );
  } // cookie

  public function put ( $key = null ) {
    parse_str(file_get_contents("php://input"), $_PUT);

    if ($key === null) {
      return $_PUT;
    }

    return ( isset($_PUT[$key]) ? $_PUT[$key] : false );
  } // put

  public function request () {
    return $this->_request;
  }

  public function load ( $component = '', $config = [], $alias = '' ) {
    if (empty($component)) return false;

    $class = "\\Seed\\Helper\\" . $this->camelfy($component);
    $alias = (!empty($alias) && is_string($alias) ? $alias : $component);

    $this->$alias = new $class( $config );

    return $this;
  } // load

  // ~~~ PRIVATE METHODS ~~~

  private function camelfy ( $str, $first_lower = false ) {
    if ( empty($str) ) {
      return '';
    }

    $worldCounter = 0;

    return implode(
      '',
      array_map(
        function ($el) use (&$worldCounter, $first_lower) {
          return $first_lower === true && $worldCounter++ === 0
            ? $el
            : ucfirst($el);
        },
        explode('-', $str)
      )
    );
  } // camelfy

  private function setPageHeaders () {
    header("Access-Control-Allow-Origin: {$this->_allowed_origin}");
    header("Content-language: {$this->_language}");

    if (count($this->_allowed_methods) > 0) {
      header("Access-Control-Allow-Methods: " . implode(', ', $this->_allowed_methods));
    }

    if (count($this->_allowed_headers) > 0) {
      header("Access-Control-Allow-Headers: " . implode(', ', $this->_allowed_headers));
    }

    // is cache allowed
    if ($this->_cache === true) {
      header("Cache-Control: max-age={$this->_cache_max_age}");
      header('Expires: '.gmdate('D, d M Y H:i:s', time() + $this->_cache_max_age) .' GMT');
    }

    // when cache is not allowed, adds necessary headers to force browser ignore caching
    else {
      header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
      header("Pragma \"no-cache\"");
      header("Expires \"Wed, 11 Jan 1984 05:00:00 GMT\"");
    }
  } // setPageHeaders

  private function buildRequest () {
    // define base path
    $patt = str_replace(array('\\',' '), array('/','%20'), dirname($_SERVER['SCRIPT_NAME']));

    // retrieve requested uri
    $this->_uri = isset($_SERVER['REQUEST_URI'])
      ? $_SERVER['REQUEST_URI']
      : '/';

    // remove query-string (if any)
    if (strpos($this->_uri, '?') !== false) {
      $this->_uri = substr($this->_uri, 0, strpos($this->_uri, '?'));
    }

    // remove base path from uri
    $this->_uri = preg_replace('@^' . $patt . '@', '', $this->_uri);

    // Identify the request method
    $this->_method  = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');

    // remove trailing slashes to easily match paths.
    // it must be done before adding a "root" slash, otherwise it breaks the router for (hidden) index pages.
    $this->_uri = preg_replace('/\/$/', '', $this->_uri);

    // add a initial (root) slash case it's not present
    if ( !preg_match('/^\//', $this->_uri) ) {
      $this->_uri = "/{$this->_uri}";
    }

    // explode arguments
    $args = explode('/', $this->_uri);

    // filter empty arguments
    // if there's empty argument, it means the uri has finished in a slash
    $args = array_filter($args, function ($el) { return !empty($el) && !is_null($el); });

    // has args only one element and is it empty?
    // means it's the start end point (root)
    if (count($args) === 1 && empty($args[0])) {
      $args[0] = '/';
    }

    // extract uri parts
    $endpoint = array_shift($args);
    $verb = array_shift($args);
    $id = null;

    // is verb and ID?
    if (is_numeric($verb)) {
      $id = $verb;
      $verb = null;
    }

    // does arguments can be set in pairs?
    if (count($args) % 2 === 0) {
      // fetches keys from args
      $args_keys = array_filter($args, function ($k) use (&$args) {
        $k = key($args); next($args);
        return !($k & 1);
      });

      // fetches values from args
      $args_values = array_filter($args, function ($k) use (&$args) {
        $k = key($args); next($args);
        return $k & 1;
      });

      // makes it an assossiative array
      $args = array_merge( $args, @array_combine($args_keys, $args_values) );
    }

    $this->_request = (object) [
      'base'      => Http::getBaseUrl(),
      'method'    => $this->_method,
      'endpoint'  => $endpoint,
      'verb'      => $verb,
      'id'        => $id,
      'args'      => $args
    ];
  } // buildRequest

} // class
