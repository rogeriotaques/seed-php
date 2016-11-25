<?php

 /* --------------------------------------------------------
 | Seed PHP - Rest Router
 |
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 |
 | It's the core engine of this framework. 
 * -------------------------------------------------------- */

namespace Core;

defined('ENV') or die('Direct script access is not allowed!');

use Libraries\Logger;

class RestRouter {

  // request headers
  protected $headers = [];

  // request method 
  protected $method;

  // requested resource
  // it's the first part of uri (resource[/verb|/ID]/key/value)
  protected $resource;

  // requested verb
  // it's (usually) the second part of uri (resource[/verb|/ID]/key/value).
  // eventually, verb can be omitted and and ID given in its place.
  protected $verb;

  // arguments given in the uri.
  // it's the key/value part from (resource[/verb|/ID]/key/value)
  protected $args = [];

  // the logger class 
  protected $logger;

  // special routes which are defined in 'config/routes.php'
  protected $routes = [];

  // the original given URI
  private $_uri;

  // user's callback for first call
  private $_callback;

  // a flag that indicates whenever an special route was found and is been tried
  private $_using_special_route = false;

  // HTTP Status Codes 
  const _CONTINUE = 100;
  const _SWITCHING_PROTOCOLS = 101;
  const _OK = 200;
  const _CREATED = 201;
  const _ACCEPTED = 202;
  const _NON_AUTHORITATIVE_INFORMATION = 203;
  const _NO_CONTENT = 204;
  const _RESET_CONTENT = 205;
  const _PARTIAL_CONTENT = 206;
  const _MULTIPLE_CHOICES = 300;
  const _MOVED_PERMANENTLY = 301;
  const _MOVED_TEMPORARILY = 302;
  const _SEE_OTHER = 303;
  const _NOT_MODIFIED = 304;
  const _USE_PROXY = 305;
  const _BAD_REQUEST = 400;
  const _UNAUTHORIZED = 401;
  const _PAYMENT_REQUIRED = 402;
  const _FORBIDDEN = 403;
  const _NOT_FOUND = 404;
  const _METHOD_NOT_ALLOWED = 405;
  const _NOT_ACCEPTABLE = 406;
  const _PROXY_AUTHENTICATION_REQUIRED = 407;
  const _REQUEST_TIMEOUT = 408;
  const _CONFLICT = 409;
  const _GONE = 410;
  const _LENGTH_REQUIRED = 411;
  const _PRECONDITION_FAILED = 412;
  const _REQUEST_ENTITY_TOO_LARGE = 413;
  const _REQUEST_URI_TOO_LARGE = 414;
  const _UNSUPPORTED_MEDIA_TYPE = 415;
  const _INTERNAL_SERVER_ERROR = 500;
  const _NOT_IMPLEMENTED = 501;
  const _BAD_GATEWAY = 502;
  const _SERVICE_UNAVAILABLE = 503;
  const _GATEWAY_TIMEOUT = 504;
  const _HTTP_VERSION_NOT_SUPPORTED = 505;

  function __construct () {
    global $routes, $cfg;

    // initialize the logger
    $this->logger = new Logger( $cfg['logger'][ENV] );

    $this->routes = $routes;
    $routes = null;
  }

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
  public function run( $arg, $callback = null ) {

    $this->_uri = $arg;
    $this->_callback = $callback;

    $arg = explode('/', $arg);

    if (count($arg) === 1 && empty($arg[0])) {

      // is there a callback to call?
      if (!is_null($callback)) {
        // is the callback callable?
        if (is_callable($callback)) {
          call_user_func($callback);
          exit;
        } 
      }

      self::response(400); // bad request
      exit;

    } elseif (count($arg) === 1) {
      $arg[1] = 'index';
    }

    // put all headers in lowercase
    foreach(getallheaders() as $k => $v) {
      $this->headers[ strtolower($k) ] = $v;
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
    $this->call();

  } // run 

  /**
  * Does the magic ...
  */ 
  private function call () {

    // is it an OPTIONS request?
    // it's used to confirm if app accept CORS calls
    if ($this->method === 'OPTIONS') {
      return self::response(200); // do accept it
    }

    $this->logger
      ->endpoint( getenv('REQUEST_URI') )
      ->resource( "{$this->method}: {$this->verb}" )
      ->requestIP( self::getClientIP() )
      ->requestHeader( $this->headers );
      
    switch ($this->method) {
      case 'GET': $this->logger->requestData( $this->args ); break;
      case 'PUT': $this->logger->requestData( self::put() ); break;
      case 'POST': $this->logger->requestData( self::post() ); break;
    }

    // define the class name and namespace
    $class = 'Controllers\\' . $this->resource;

    try {

      // get real possible method name (verb+method)
      // $func = strtolower("{$this->verb}_{$this->method}");
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
        $this->logger->responseCode(400)->log();
        return self::response(400); // bad request
      }

      // call it 
      $class = new $class;
      $response = call_user_func_array([$class, $func], $call_args);

      $this->logger
        ->responseCode( $response['code'] )
        ->responseData( $response['result'] )
        ->log();

    } catch (\ReflectionException $e) {

      if ( $this->_using_special_route ) {
        $this->logger->responseCode(501)->log();
        return self::response(501); // not implemented
      }

      // before return an error, first, try to match any special route
      $match = false;

      foreach ($this->routes as $route_from => $route_to) {

        $matches = [];
        preg_match( '|' . $route_from . '|', $this->_uri, $matches );

        if ( count($matches) > 0 ) {
          $match = preg_replace('|' . $route_from . '|', $route_to, $this->_uri);
          break;
        }
      }

      // var_dump($match); die;

      if ( $match !== false ) {

        // try again with special route ...
        $this->_using_special_route = true;
        $this->run($match, $this->_callback);

      } else {
        $this->logger->responseCode(501)->log();
        return self::response(501); // not implemented
      }

    }

  } // call

  /**
   * Standardise the response.
   */
  public static function response ( $code, $response = [], $extra = [] ) {
    // retrieve http status code 
    $status = self::getHTTPStatus($code);
    $result = array_merge($extra, ['status' => $status['code'], 'data' => $response]);

    // if it's an error merge with error data 
    if ($code >= 400) {
      $result = array_merge($extra, ['error' => $status['code'], 'message' => $status['message'], 'responseJSON' => $response]);
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
  public static function post ( $key = null ) {
    if ($key === null) {
      return $_POST;
    }

    return ( isset($_POST[$key]) ? $_POST[$key] : false );
  } // post

  /**
   * Retrieve a get value. If key is not given, retrieve a list of gotten values.
   */
  public static function get ( $key = null ) {
    if ($key === null) {
      return $_GET;
    }

    return ( isset($_GET[$key]) ? $_GET[$key] : false );
  } // get

  /**
   * Retrieve a put value. If key is not given, retrieve a list of put values.
   */
  public static function put ( $key = null ) {
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
  public static function data ( $method = 'GET', $key = null ) {
    switch (strtoupper($method)) {
      case 'PUT': return self::put($key);
      case 'POST': return self::post($key);
      case 'GET': return self::get($key);
    }
  } // data

  /**
   * Retrieve the right http text string according to given code.
   * @param {integer} $code - Default is 200.
   * @return {string}
   */
  public static function getHTTPStatus ( $code = 200 ) {

      $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

      switch ($code) {
        case 100: $text = 'Continue'; break;
        case 101: $text = 'Switching Protocols'; break;
        case 200: $text = 'OK'; break;
        case 201: $text = 'Created'; break;
        case 202: $text = 'Accepted'; break;
        case 203: $text = 'Non-Authoritative Information'; break;
        case 204: $text = 'No Content'; break;
        case 205: $text = 'Reset Content'; break;
        case 206: $text = 'Partial Content'; break;
        case 300: $text = 'Multiple Choices'; break;
        case 301: $text = 'Moved Permanently'; break;
        case 302: $text = 'Moved Temporarily'; break;
        case 303: $text = 'See Other'; break;
        case 304: $text = 'Not Modified'; break;
        case 305: $text = 'Use Proxy'; break;
        case 400: $text = 'Bad Request'; break;
        case 401: $text = 'Unauthorized'; break;
        case 402: $text = 'Payment Required'; break;
        case 403: $text = 'Forbidden'; break;
        case 404: $text = 'Not Found'; break;
        case 405: $text = 'Method Not Allowed'; break;
        case 406: $text = 'Not Acceptable'; break;
        case 407: $text = 'Proxy Authentication Required'; break;
        case 408: $text = 'Request Time-out'; break;
        case 409: $text = 'Conflict'; break;
        case 410: $text = 'Gone'; break;
        case 411: $text = 'Length Required'; break;
        case 412: $text = 'Precondition Failed'; break;
        case 413: $text = 'Request Entity Too Large'; break;
        case 414: $text = 'Request-URI Too Large'; break;
        case 415: $text = 'Unsupported Media Type'; break;
        case 500: $text = 'Internal Server Error'; break;
        case 501: $text = 'Not Implemented'; break;
        case 502: $text = 'Bad Gateway'; break;
        case 503: $text = 'Service Unavailable'; break;
        case 504: $text = 'Gateway Time-out'; break;
        case 505: $text = 'HTTP Version not supported'; break;
        default: exit('Unknown http status code "' . htmlentities($code) . '"'); break;
      }
      
      return ['protocol' => $protocol, 'code' => $code, 'message' => $text];

    } // getHTTPStatus

    /**
    * Returns the project base URL.
    * @param {variant} [$protocol] - Default is false. Should be 'http', 'https' or false.
    * @return {string}  
    */
    public static function getBaseUrl ( $protocol = false ) {
        global $cfg;

        return sprintf(
          "%s://%s",
          (
            $protocol !== false 
              ? $protocol 
              : isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http'
          ),
          $cfg['base-url'][ENV]
        );

      } // getBaseUrl

      /**
       * Return the client IP address.
       * @return {string}
       */
      public static function  getClientIP () {
          $ipaddress = '';

          if (getenv('HTTP_CLIENT_IP')) {
              $ipaddress = getenv('HTTP_CLIENT_IP');
          } else if (getenv('HTTP_X_FORWARDED_FOR')) {
              $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
          } else if (getenv('HTTP_X_FORWARDED')) {
              $ipaddress = getenv('HTTP_X_FORWARDED');
          } else if (getenv('HTTP_FORWARDED_FOR')) {
              $ipaddress = getenv('HTTP_FORWARDED_FOR');
          } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
          } else if (getenv('REMOTE_ADDR')) {
              $ipaddress = getenv('REMOTE_ADDR');
          } else {
              $ipaddress = 'UNKNOWN';
          } 
          
          return $ipaddress;
      } // getClientIP

} // class 