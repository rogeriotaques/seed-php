<?php 

 /* --------------------------------------------------------
 | Seed-PHP Microframework.
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.2.4
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

  // define the default output type
  protected $_output_type = 'json';

  // the callback for when an error is held
  private $_error_handler = false;

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

      // sanitize the regular expression
      $route = str_replace(['/', '.', '@'], ['\\/', '\\.', '\\@'], $route);

      // add new route 
      $this->_routes[ $m ][] = (object) [
        'uri' => $route,
        'callback' => $callback
      ];
    }

    // make it chainable
    return $this;
  } // route 

  public function response ( $code = 200, $response = [], $output = null ) {
    if (is_null($output)) {
      $output = $this->_output_type;
    }

    // identify the  http status code 
    $status = Http::getHTTPStatus( $code );

    $result = [ 
      'status'  => $status['code'], 
      'message' => $status['message'] 
    ];

    // if it's an error merge with error data 
    if ($code >= Http::_BAD_REQUEST) {
      $result['error'] = true;
    }

    // $response should be an array. Whenever it isn't, try to convert it. 
    // If impossible to convert, just ignores it.  
    if ( is_object($response) ) {
      $response = (array) $response;
    } elseif ( is_string($response) ) {
      $response = [ $response ];
    } elseif ( !is_array($response) ) {
      $response = [];
    }

    // merge response and result 
    $result = array_merge($result, $response);

    // allow enduser to customize the return structure for status
    if ( isset($_GET['_router_status']) && !empty($_GET['_router_status']) ) {
      if ( isset($result['status']) ) {
        $result[ $_GET['_router_status'] ] = $result['status'];
        unset($result['status']);
      }
    }

    // allow enduser to customize the return structure for message
    if ( isset($_GET['_router_message']) && !empty($_GET['_router_message']) ) {
      if ( isset($result['message']) ) {
        $result[ $_GET['_router_message'] ] = $result['message'];
        unset($result['message']);
      }
    }

    // allow enduser to customize the return structure for data
    if ( isset($_GET['_router_data']) && !empty($_GET['_router_data']) ) {
      if ( isset($result['data']) ) {
        $result[ $_GET['_router_data'] ] = $result['data'];
        unset($result['data']);
      }
    }

    // is output required?
    if ($output !== false) {
      header("{$status['protocol']} {$status['code']} {$status['message']}");

      if ($this->_cache === true) {
        header('ETag: ' . md5( !is_string($result) ? json_encode($result) : $result )); // this help on caching
      } 
    }

    // what kind of output is expected?
    switch ( strtolower($output) ) {
      case 'xml':
        header("Content-Type: application/xml");

        // translate json into xml object 
        $xml = new \SimpleXMLElement('<response />');
        $xml = $this->json2xml($xml, $result);

        // finally convert it to string for proper replacements
        echo $xml->asXML();
        break;

      case 'json':
        // set proper headers 
        header("{$status['protocol']} {$status['code']} {$status['message']}");
        header("Content-Type: application/json");

        // convert result into string
        $result = json_encode($result);

        echo $result;
        break;
      
      default: 
        // anything else, including "false"
        // do nothing. do not output, just return it.
    }

    // return as object the response 
    return $result;
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

  public function setOutputType( $type = 'json' ) {
    if (!empty($output)) {
      $this->_output_type = $output;
    }
  } //setOutputType

  public function onFail ( $callback = false ) {
    if ($callback !== false && is_callable($callback)) {
      $this->_error_handler = $callback;
    }
  } // onFail

  // ~~~ PROTECTED ~~~

  protected function dispatch ( $args = [] ) {
    $matches = [];
    $matched_callback = false; 

    // is there a matching route?
    if ( isset($this->_routes[$this->_method]) ) {
      foreach ($this->_routes[$this->_method] as $route) {
        // echo '<pre>', $route->uri, ' ::: ', $this->_uri;
        if ( @preg_match("@^{$route->uri}$@", $this->_uri, $matches) ) {
          // echo " -> MATCHED";
          $matched_callback = $route->callback;
          break;
        }
        // echo "</pre><br>";
      }
    }

    // echo '<pre>', var_dump($matches), '</pre><br >'; die;

    if (count($matches) === 0) {
      if ( $this->_error_handler === false ) {
        return $this->response(Http::_NOT_IMPLEMENTED);
      } else {
        return call_user_func($this->_error_handler, (object) Http::getHTTPStatus( Http::_NOT_IMPLEMENTED ));    
      }
    } 

    if ($matched_callback !== false && is_callable($matched_callback)) {
      return call_user_func($matched_callback, $args);
    }

    return true;
  } // dispatch

  // ~~~ PRIVATE ~~~

  private function json2xml( &$xml, $data ) {

    // exit when data is empty.
    if (is_null($data)) {
      return $xml;
    }

    // runs thru data to build xml 
    foreach ($data as $dk => $dv) {

      // node data can be an array/ object 
      if ( is_array($dv) ) {

        // is the key a string?
        if ( !is_numeric($dk) ) {

          // eventually it's possible that nodes have properties
          // whenever it has, isolate properties for post use. 
          if (strpos($dk, ' ') !== false) {
            $props = explode(' ', $dk);
            $dk = array_shift($props); 
          }

          // create a new node 
          $node = $xml->addChild($dk);

          // new node should have attributes? appends it ...   
          if (isset($props) && count($props) > 0) {
            foreach ($props as $prop) {
              $prop = explode('=', $prop);
              $prop[1] = strpos($prop[1], '"') === 0 ? substr($prop[1], 1, strlen($prop[1]) - 2) : $prop[1];
              $node->addAttribute($prop[0], $prop[1]);
            }
          }

          // recursive call for subnodes
          // giving the most recent node created
          $this->json2xml($node, $dv);
        } else {
          // recursive call for subnodes 
          $this->json2xml($xml, $dv);
        }
      } else {

        $xml->addChild($dk, htmlspecialchars($dv));
        
      }
    }

    // return xml object 
    return $xml;

  } // json2xml

} // class 
