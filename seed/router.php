<?php 

 /* --------------------------------------------------------
 | Seed-PHP Microframework.
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1.0
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 * -------------------------------------------------------- */

namespace Seed;

defined('SEED') or die('Direct script access is not allowed!');

use Seed\Helper\Http;

class Router {
  // request method 
  protected $_method = 'GET';

  // app routes
  protected $_routes = [];

  // app allowed methods 
  protected $_allowed_methods = [ 'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH' ];

  // app allowed headers 
  protected $_allowed_headers = [ 'Origin', 'Content-Type', 'X-Requested-With' ];

  // app allowed headers 
  protected $_allowed_origin = '*';

  // app page cache
  protected $_cache = true; 

  // app page cache max age
  protected $_cache_max_age = 3600; // default is one hour 

  // app page language
  protected $_language = 'en'; // english 

  // app page charset
  protected $_charset = 'utf8'; 

  // requested uri 
  protected $_uri = '';

  // ~~~ PUBLIC ~~~

  public function route ( $route = 'GET /', $callback = false ) {
    $method = ['GET'];

    if (strpos($route, ' ') !== false) {
      list($method, $route) = explode(' ', $route);
      $method = explode('|', $method);
    } 

    foreach ($method as $m) {
      // is there a route set for given method?
      if (!isset($this->_routes[ $m ])) {
        $this->_routes[ $m ] = [];
      }

      // add new route 
      $this->_routes[ $m ][] = (object) [
        'uri' => $route,
        'callback' => $callback
      ];
    }

    // make it chainable
    return $this;
  } // route 

  public function response ( $code = 200, $response = [], $output = 'json' ) {
    // identify the  http status code 
    $status = Http::getHTTPStatus( $code );

    $result = [ 'status' => $status['code'], 'message' => $status['message'] ];

    // if it's an error merge with error data 
    if ($code >= Http::_BAD_REQUEST) {
      $result['error'] = true;
    }

    // merge response and result 
    $result = array_merge($result, $response);
     
    header("{$status['protocol']} {$status['code']} {$status['message']}");

    if ($this->_cache === true) {
      header('ETag: ' . md5( !is_string($result) ? json_encode($result) : $result )); // this help on caching
    } 

    switch ( strtolower($output) ) {
      case 'xml':

      case 'json':
      default: // json 
        // convert result into string
        $result = json_encode($result);

        // set proper headers 
        header("{$status['protocol']} {$status['code']} {$status['message']}");
        header("Content-Type: application/json");

        echo $result;
    }

    // return 
    return [
      'code' => $code,
      'result' => $result
    ];
  } // response

  public function setAllowedMethod ( $method = '', $merge = true ) {
    if (!empty($method)) {
      if (!$merge) {
        $this->_allowed_methods = [];
      }

      if (is_array($method)) {
        $this->_allowed_methods = array_merge($this->_allowed_methods, $method);
      } else {
        $this->_allowed_methods[] = $method;
      }   
    }

    return $this;
  } // setAllowedMethod

  public function setAllowedHeader ( $header = '', $merge = true ) {
    if (!empty($header)) {
      if (!$merge) {
        $this->_allowed_headers = [];
      }

      if (is_array($header)) {
        $this->_allowed_headers = array_merge($this->_allowed_headers, $header);
      } else {
        $this->_allowed_headers[] = $header;
      }   
    }

    return $this;
  } // setAllowedHeader

  public function setAllowedOrigin ( $origin = '' ) {
    if (!empty($origin)) {
      $this->_allowed_origin = $origin;
    }
    
    return $this;
  } // setAllowedOrigin

  public function setCache( $flag = true, $max_age = 3600 ) {
    $this->_cache = $flag;
    $this->_cache_max_age = $max_age;
    return $this;
  } // setFlag

  public function setLanguage ( $lang = 'en' ) {
    if (!empty($lang)) {
      $this->_language = $lang;
    }

    return $this;
  } // set Language

  public function setCharset ( $charset = 'utf8' ) {
    if (!empty($charset)) {
      $this->_charset = $charset;
    }

    return $this;
  } // setCharset

  // ~~~ PROTECTED ~~~

  protected function dispatch ( $args = [] ) {
    $matches = [];
    $matched_callback = false; 

    // is there a matching route?
    if ( isset($this->_routes[$this->_method]) ) {
      foreach ($this->_routes[$this->_method] as $route) {
        if ( preg_match($this->regexify($route->uri, 'end'), $this->_uri, $matches) ) {
          $matched_callback = $route->callback;
          break;
        }
      }
    }

    if (count($matches) === 0) {
      return $this->response(Http::_NOT_IMPLEMENTED);
    } 

    if ($matched_callback !== false && is_callable($matched_callback)) {
      return call_user_func($matched_callback, $args);
    }

    return true;
  } // dispatch

  // ~~~ PRIVATE ~~~

  private function regexify ( $str = '', $pos = false ) {
    $str = str_replace(['/', '_', '.'], ['\\/', '\\_', '\\.'], $str);
    return '|' . ($pos == 'start' ? '^' : '') . $str . ($pos == 'end' ? '$' : '') . '$|';
  } // regexify
} // class 
