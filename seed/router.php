<?php

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.3
 | @license MIT
 | @see http://github.com/rogeriotaques/php-api-kit
 * -------------------------------------------------------- */

namespace Seed;

defined('ENV') or die('Direct script access is not allowed!');

use Seed\Nucleos;
use Seed\Libraries\Logger;
use Seed\Libraries\Http;

/**
 * Singleton
 */
class Router {
  private static $instance;

  private $config;

  // request headers
  protected $headers = [];

  // request method 
  protected $method = 'GET';

  // requested resource
  // it's the first part of uri (resource[/verb|/ID]/key/value)
  protected $resource = false;

  // requested verb
  // it's (usually) the second part of uri (resource[/verb|/ID]/key/value).
  // eventually, verb can be omitted and and ID given in its place.
  protected $verb = '';

  // arguments given in the uri.
  // it's the key/value part from (resource[/verb|/ID]/key/value)
  protected $args = [];

  // the logger class 
  protected $logger = false;

  // the original given URI
  private $_uri = '';

  // a flag that indicates whenever an special route was found and is been tried
  private $_using_special_route = false;

  // special routes
  private $_routes = [];

  function __construct () {}

  public static function getInstance ( $arg = '' ) {
    if (is_null(self::$instance)) {
      self::$instance = new Router();

      if (@include('config/routes.php')) {
        self::$instance->routes = $routes;
        unset($routes);
      }

      $ncl = new Nucleos();
      self::$instance->config = $ncl->getConfig();

      // set the requested uri
      if (!empty($arg)) {
        self::$instance->_uri = $arg;
      }

      if (self::$instance->config['log'] === true) {
        // initialize the logger
        self::$instance->logger = new Logger( self::$instance->config['database'][ENV] );
      }
    }

    return self::$instance;
  } // getInstance

  /**
   * Transform a string in CamelCase.
   * @param {string} $str
   * @param {boolean} $first_lower - A flag that indicates whenever the first char should be upper or lower case.
   * @return {string}
   */
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

  /**
   * Starts the magic ...
   */
  public function run () {
    // let's start working with the uri
    $arg = explode('/', $this->_uri);

    // is there an URI set?
    if (count($arg) === 1 && empty($arg[0])) {
      // whenever there's not given resource and endpoint (verb)
      // we assume it's index resource and endpoint 
      $arg[0] = $arg[1] = 'index';
    } elseif (count($arg) === 1) {
      $arg[1] = 'index';
    }

    // put all headers in lowercase
    foreach(getallheaders() as $k => $v) {
      $this->headers[ $k ] = $v;
    }

    // retrieve requested method 
    $this->method = $_SERVER['REQUEST_METHOD'];

    // define the resource
    $this->resource = $this->camelfy($arg[0]);
    
    // removes resource
    array_shift($arg); 

    // is a second arg been passed?
    if ( isset($arg[0]) && !empty($arg[0]) ) {
      // is it numeric? 
      // if so, we consider it as an ID.
      if ( is_numeric($arg[0]) ) {
        $this->args['id'] = $arg[0];
        $this->verb = 'index';
      } 
      
      // if not, it's the verb
      else {
        // define which verb is been used
        // whenever there's no verb set, take index as default 
        $this->verb = $this->camelfy($arg[0]);
      }
    } else {
      $this->verb = 'index';
    }

    // removes verb or ID
    array_shift($arg);

    // filter empty arguments
    // if there's empty argument, it means the uri has finished in a slash
    $arg = array_filter($arg, function ($el) { return !empty($el); });

    // fetches keys from args
    $keys = array_filter($arg, function ($k) use (&$arg) {
      $k = key($arg); next($arg);
      return !($k & 1);
    });

    // fetches values from args
    $values = array_filter($arg, function ($k) use (&$arg) {
      $k = key($arg); next($arg);
      return $k & 1;
    });

    // makes it an assossiative array
    $this->args = array_merge( $this->args, @array_combine($keys, $values) );

    // do the call ... 
    return $this->execute();
  } // run 

  /**
  * Does the magic ...
  */ 
  private function execute () {
    // let's allow CORS (the most essential thing for open APIs)
    if ($this->config['cors'] === true) {
      header("Access-Control-Allow-Origin: *");
    }
    
    // define app language
    header("Content-language: {$this->config['language']}");

    // are there methods allowed?
    if (count($this->config['methods']) > 0) {
      header("Access-Control-Allow-Methods: " . implode(', ', $this->config['methods']));
    }

    // are there headers allowed?
    if (count($this->config['headers']) > 0) {
      header("Access-Control-Allow-Headers: " . implode(', ', $this->config['headers']));
    }

    // is cache allowed
    if ($this->config['cache'] === true) {
      header("Cache-Control: max-age={$this->config['cache-max-age']}");
      header('Expires: '.gmdate('D, d M Y H:i:s', time() + $this->config['cache-max-age'] ).' GMT');
    }

    // is it an OPTIONS request?
    // it's used to confirm if app accept CORS calls
    if ($this->method === 'OPTIONS') {
      return $this->response(Http::_OK); // do accept it
    }

    // should we log it?
    if ($this->config['log'] === true) {
      $this->logger
        ->endpoint( getenv('REQUEST_URI') )
        ->resource( "{$this->method}: {$this->verb}" )
        ->requestIP( Http::getClientIP() )
        ->requestHeader( $this->headers );
      
      switch ($this->method) {
        case 'GET': $this->logger->requestData( $this->args ); break;
        case 'PUT': $this->logger->requestData( $this->put() ); break;
        case 'POST': $this->logger->requestData( $this->post() ); break;
      }
    }

    // define the class name and namespace
    $class = 'Controllers\\' . $this->resource;

    try {
      // get real possible method name (verb+method)
      $func = "{$this->verb}_" . strtolower($this->method);

      // get reflection information 
      $refm  = new \ReflectionMethod($class, $func);

      $call_args = [];
      $call_args_required_count = 0;
      
      // define how many arguments are required and 
      // prepare the callable list of named arguments.
      foreach($refm->getParameters() as $arg) {
        // count not optional args
        if (!$arg->isOptional()) {
          $call_args_required_count++;
        }

        // put named arguments in order
        if (isset($this->args[$arg->name])) {
          $call_args[] = $this->args[$arg->name];
        }
      }

      // not given the right number of required arguments?
      if ($call_args_required_count > count($call_args)) {
        if ($this->config['log'] === true) {
          $this->logger->responseCode(Http::_BAD_REQUEST)->log();
        }
        return $this->response(Http::_BAD_REQUEST); // bad request
      }

      // call it 
      $class = new $class;
      $response = call_user_func_array([$class, $func], $call_args);

      if ($this->config['log'] === true) {
        $this->logger
          ->responseCode( $response['code'] )
          ->responseData( $response['result'] )
          ->log();
      }
    } catch (\ReflectionException $e) {
      
      if ( $this->_using_special_route ) {
        if ($this->config['log'] === true) {
          $this->logger->responseCode(Http::_NOT_IMPLEMENTED)->log();
        }
        die( $e->getMessage() );
        return $this->response(Http::_NOT_IMPLEMENTED); // not implemented
      }

      // before return an error, first, try to match any special route
      $match = false;

      if ( !is_null($this->routes) ) {
        foreach ($this->routes as $route_from => $route_to) {

          $matches = [];
          preg_match( '|' . $route_from . '|', $this->_uri, $matches );

          if ( count($matches) > 0 ) {
            $match = preg_replace('|' . $route_from . '|', $route_to, $this->_uri);
            break;
          }
        }
      }

      // var_dump($match); die;

      if ( $match !== false ) {
        // try again with special route ...
        $this->_using_special_route = true;
        $this->_uri = $match;
        $this->run();
      } else {
        if ($this->config['log'] === true) {
          $this->logger->responseCode(Http::_NOT_IMPLEMENTED)->log();
        }
        return $this->response(Http::_NOT_IMPLEMENTED); // not implemented
      }
    }
  } // execute

  /**
   * Standardise the response.
   */
  public function response ( $code, $response = [], $extra = [] ) {
    $_variable_names = [
      'return_status' => 'status',
      'return_data'   => 'data'
    ];

    // enable the user to modify status property name from return object 
    if (isset($_GET['_router_status']) && is_string($_GET['_router_status'])) {
      $_variable_names['return_status'] = $_GET['_router_status'];
    }

    // enable user to modify data property name from return object
    if (isset($_GET['_router_data']) && is_string($_GET['_router_data'])) {
      $_variable_names['return_data'] = $_GET['_router_data'];
    }

    // identify the  http status code 
    $status = Http::getHTTPStatus($code);

    // prepare the returning object 
    $result = array_merge($extra, [
      $_variable_names['return_status'] => $status['code'], 
      $_variable_names['return_data'] => $response
    ]);

    // if it's an error merge with error data 
    if ($code >= Http::_BAD_REQUEST) {
      unset($result[ $_variable_names['return_data'] ]);
      
      if (!is_array($response)) {
        $response = (array) $response;
      }

      $result = array_merge($result, $response, ['message' => $status['message']]);
      // $result = array_merge($extra, ['error' => $status['code'], 'message' => $status['message'], 'responseJSON' => $response]);
    }

    // convert result into string
    $result = json_encode($result);

    // set proper headers 
    header("{$status['protocol']} {$status['code']} {$status['message']}");
    header('ETag: ' . md5( $result )); // this help on caching
    
    header("Content-Type: application/json");

    // output the result content
    echo $result;

    // return 
    return [
      'code' => $code,
      'result' => $result
    ];
  } // response

  /**
   * Retrieve a header. If key is not given, retrieve a list of headers.
   */
  public function getHeader ( $key = null ) {
    if ($key === null) {
      return $this->headers;
    }

    return ( isset($this->headers[$key]) ? $this->headers[$key] : false );
  } // getHeader

  /**
   * Retrieve a post value. If key is not given, retrieve a list of posted values.
   */
  public function post ( $key = null ) {
    if ($key === null) {
      return $_POST;
    }

    return ( isset($_POST[$key]) ? $_POST[$key] : false );
  } // post

  /**
   * Retrieve a get value. If key is not given, retrieve a list of gotten values.
   */
  public function get ( $key = null ) {
    if ($key === null) {
      return $_GET;
    }

    return ( isset($_GET[$key]) ? $_GET[$key] : false );
  } // get

  /**
   * Retrieve a put value. If key is not given, retrieve a list of put values.
   */
  public function put ( $key = null ) {
    parse_str(file_get_contents("php://input"), $PUT);

    if ($key === null) {
      return $PUT;
    }

    return ( isset($PUT[$key]) ? $PUT[$key] : false );
  } // put

  /**
   * Retrieve a received data value according to request method. 
   * If key is not given, retrieve a list of put values.
   */
  public function data ( $method = 'GET', $key = null ) {
    switch (strtoupper($method)) {
      case 'PUT': return $this->put($key);
      case 'POST': return $this->post($key);
      case 'GET': return $this->get($key);
    }
  } // data

} // class 
