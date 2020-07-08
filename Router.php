<?php

/**
 * Seed-PHP Microframework.
 * @author Rogerio Taques
 * @license MIT
 * @see http://github.com/rogeriotaques/seed-php
 */

namespace SeedPHP;

use ErrorException;
use SeedPHP\Helper\Http;

class Router
{
    /**
     * @var string request method
     * @see $_allowed_methods
     */
    protected $_method = 'GET';

    /** @var array  app routes */
    protected $_routes = [];

    /** @var array<string> app allowed methods */
    protected $_allowed_methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'];

    /** @var array<string> app allowed headers */
    protected $_allowed_headers = ['Origin', 'Content-Type', 'X-Requested-With'];

    /** @var string app allowed origin */
    protected $_allowed_origin = '*';

    /** @var boolean app page cache */
    protected $_cache = true;

    /** @var integer app page cache max age */
    protected $_cache_max_age = 3600; // default is one hour

    /** @var string app page language */
    protected $_language = 'en'; // english

    /** @var string app page charset */
    protected $_charset = 'utf8';

    /** @var string requested url */
    protected $_uri = '';

    /** @var string define the default output type */
    protected $_output_type = 'json';

    /** @var false|function the callback for when an error is held */
    protected $_error_handler = false;

    /** @var string name for the response status property */
    private $_response_property_status = 'status';

    /** @var string name for the response message property */
    private $_response_property_message = 'message';

    /** @var string name for the response data property */
    private $_response_property_data = 'data';

    /** @var string name for the response error property */
    private $_response_property_error = 'error';

    /** @var array<key => value> additional properties to be returned alognside default properties in a response */
    private $_additional_response_properties = [];

    // ~~~ PUBLIC ~~~

    /**
     * Set routes for the app.
     *
     * @param string $route
     * @param callable $callback - Optional
     * @param callable $before   - Optional. A hook to be executed before the callback.
     * @param callable $after    - Optional. A hook to be executed after the callback.
     * @return Router
     */
    public function route(string $route = 'GET /', ?callable $callback = null, ?callable $before = null, callable $after = null) : self
    {
        $method = ['GET'];

        if (strpos($route, ' ') !== false) {
            list($method, $route) = explode(' ', $route);
            $method = explode('|', $method);
        }

        foreach ($method as $m) {
            // is there a route set for given method?
            if (!isset($this->_routes[$m])) {
                $this->_routes[$m] = [];
            }

            // NOTE:
            //  Coz unicorn exists, this url sanitization was placed here!
            //  Damn, it was breaking the routing system for multiples methods and that's why it is commented now!
            // sanitize the regular expression
            // $route = str_replace(['/', '.', '@'], ['\/', '\.', '\@'], $route);

            // add new route
            $this->_routes[$m][] = (object)[
                'uri' => $route,
                'callback' => $callback,
                'before' => $before,
                'after' => $after
            ];
        }

        // make it chainable
        return $this;
    } // route

    /**
     * Complete the flow and send a response to the browser.
     *
     * @param integer   $code
     * @param array     $response
     * @param string    $output either json or xml
     * @return mixed    json|xml strings
     */
    public function response(int $code = 200, array $response = [], ?string $output = null)
    {
        if (is_null($output)) {
            $output = $this->_output_type;
        }

        // identify the  http status code
        try {
            $status = Http::getHTTPStatus($code);
        } catch (\Throwable $th) {
            // Fallback to "Internal Server Error" with the original message.
            $status = Http::getHTTPStatus(Http::_INTERNAL_SERVER_ERROR);
            $status['message'] = $th->getMessage();
        }

        $result = [
            "{$this->_response_property_status}"  => $status['code'], // aka code
            "{$this->_response_property_message}" => $status['message']
        ];

        // if it's an error merge with error data
        if ($code >= Http::_BAD_REQUEST) {
            $result[ $this->_response_property_error ] = true;
        }

        // $response should be an array. Whenever it isn't, try to convert it.
        // If impossible to convert, just ignores it.
        if (is_object($response)) {
            $response = (array) $response;
        } elseif (is_string($response)) {
            $response = [ $response ];
        } elseif (!is_array($response)) {
            $response = [];
        }

        // merge response and result
        $result = array_merge($result, $this->_additional_response_properties, $response);

        // allow enduser to customize the return structure for status
        if (isset($_GET['_router_status']) && !empty($_GET['_router_status'])) {
            if (isset($result[ $this->_response_property_status ])) {
                $result[$_GET['_router_status']] = $result[ $this->_response_property_status ];
                unset($result[ $this->_response_property_status ]);
            }
        }

        // allow enduser to customize the return structure for message
        if (isset($_GET['_router_message']) && !empty($_GET['_router_message'])) {
            if (isset($result[ $this->_response_property_message ])) {
                $result[$_GET['_router_message']] = $result[ $this->_response_property_message ];
                unset($result[ $this->_response_property_message ]);
            }
        }

        // allow enduser to customize the return structure for data
        if (isset($_GET['_router_data']) && !empty($_GET['_router_data'])) {
            if (isset($result[ $this->_response_property_data ])) {
                $result[$_GET['_router_data']] = $result[ $this->_response_property_data ];
                unset($result[ $this->_response_property_data ]);
            }
        }

        // is output required?
        if ($output !== false) {
            header("{$status['protocol']} {$status['code']} {$status['message']}");

            if ($this->_cache === true) {
                header('ETag: ' . md5(!is_string($result) ? json_encode($result) : $result)); // this help on caching
            }
        }

        // what kind of output is expected?
        switch (strtolower($output)) {
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

    /**
     * Set an allowed method.
     *
     * @param string|array<string> $method
     * @param bool $merge when true, resets the list of allowed methods
     * @return Router
     */
    public function setAllowedMethod($method = '', bool $merge = true) : self
    {
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

    /**
     * Set an allowed header.
     *
     * @param string|array<string> $header
     * @param bool $merge
     * @return Router
     */
    public function setAllowedHeader($header = '', bool $merge = true) : self
    {
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

    /**
     * Set an allowed origin. Usually a single origin.
     *
     * @param string $origin
     * @return Router
     */
    public function setAllowedOrigin(string $origin = '') : self
    {
        if (!empty($origin)) {
            $this->_allowed_origin = $origin;
        }

        return $this;
    } // setAllowedOrigin

    /**
     * Define a custom cache setting.
     *
     * @param boolean $flag
     * @param integer $max_age
     * @return Router
     */
    public function setCache(bool $flag = true, int $max_age = 3600) : self
    {
        $this->_cache = $flag;
        $this->_cache_max_age = $max_age;
        return $this;
    } // setFlag

    /**
     * Set a custom language. Defaults to 'en'.
     *
     * @param string $lang
     * @return Router
     */
    public function setLanguage(string $lang = 'en') : self
    {
        if (!empty($lang)) {
            $this->_language = $lang;
        }

        return $this;
    } // set Language

    /**
     * Set a custom charset. Defaults to 'utf8'.
     *
     * @param string $charset
     * @return Router
     */
    public function setCharset(string $charset = 'utf8'): self
    {
        if (!empty($charset)) {
            $this->_charset = $charset;
        }

        return $this;
    } // setCharset

    /**
     * Define what format the response will be given. Defaults to JSON.
     *
     * @param string $type
     * @return Router
     */
    public function setOutputType(string $type = 'json'): self
    {
        if (!empty($type)) {
            $this->_output_type = $type;
        }

        return $this;
    } //setOutputType

    /**
     * Set a custom error handler.
     *
     * @param mixed $callback Accepts function|false|null
     * @return Router
     */
    public function onFail(?callable $callback = null): self
    {
        if (!empty($callback) && is_callable($callback)) {
            $this->_error_handler = $callback;
        }

        return $this;
    } // onFail

    /**
     * Defines a custom property name to return the response http code.
     *
     * @param string $name
     * @return Router
     */
    public function setCustomPropertyStatus(string $name = 'status'): self
    {
        if (!empty($name)) {
            $this->_response_property_status = $name;
        }

        return $this;
    } // setCustomPropertyStatus

    /**
     * Defines a custom property name to return the response message.
     *
     * @param string $name
     * @return Router
     */
    public function setCustomPropertyMessage(string $name = 'message'): self
    {
        if (!empty($name)) {
            $this->_response_property_message = $name;
        }

        return $this;
    } // setCustomPropertyMessage

    /**
     * Defines a custom property name to return the response data.
     *
     * @param string $name
     * @return Router
     */
    public function setCustomPropertyData(string $name = 'data'): self
    {
        if (!empty($name)) {
            $this->_response_property_data = $name;
        }

        return $this;
    } // setCustomPropertyData

    /**
     * Defines a custom property name to return the error status.
     *
     * @param string $name
     * @return Router
     */
    public function setCustomPropertyError(string $name = 'error'): self
    {
        if (!empty($name)) {
            $this->_response_property_error = $name;
        }

        return $this;
    } // setCustomPropertyError

    /**
     * Append global additional properties to be returned alongside any response by default.
     *
     * @param string $key
     * @param mixed  $value Accepts string|integer|boolean|function
     * @param bool   $reset when true, resets the list of additional properties
     * @return Router
     */
    public function setAdditionalResponseProperty(string $key = '', $value = '', bool $reset = false): self
    {
        if (!empty($key)) {
            if ($reset) {
                $this->_additional_response_properties = [];
            }

            $this->_additional_response_properties[$key] = $value;
        }

        return $this;
    } // setAdditionalResponseProperty


    // ~~~ PROTECTED ~~~

    /**
     * Require all route files from a given path.
     *
     * @param string $path
     * @return void
     */
    protected function readRoutesFrom(string $path = ''): void
    {
        if (empty($path)) {
            throw new ErrorException("SeedPHP :: Router : Path must be given.", Http::_BAD_REQUEST);
        }

        $path = preg_replace('/\/$/', '', $path) . '/*.php';

        // Include all files from the given path.
        // This removes the need to include them one bye one.
        // @since 1.7.0
        foreach (glob($path) as $file) {
            require_once $file;
        }
    } // readRoutesFrom

    /**
     * Dispatches the router actions.
     *
     * @param array $args
     * @return mixed bool|null|string
     */
    protected function dispatch(array $args = [])
    {
        $matches             = [];
        $matched_callback    = null;
        $matched_hook_before = null;
        $matched_hook_after  = null;

        // echo "METHOD ", $this->_method, "<br />\n";

        // is there a matching route?
        if (isset($this->_routes[$this->_method])) {
            // echo "Found routes for ", $this->_method, "\n";

            foreach ($this->_routes[$this->_method] as $route) {
                // echo $route->uri, ' ::: ', $this->_uri, "<br />\n";

                if (@preg_match("@^{$route->uri}$@", $this->_uri, $matches)) {
                    // echo " -> MATCHED";
                    $matched_callback    = $route->callback;
                    $matched_hook_before = $route->before;
                    $matched_hook_after  = $route->after;
                    break;
                }
                
                // echo "<br />\n";
            }
        }

        // echo '<pre>', var_dump($matches), '</pre><br >', "\n";
        // die;

        if (count($matches) === 0) {
            if ($this->_error_handler !== false) {
                return call_user_func($this->_error_handler, (object) Http::getHTTPStatus(Http::_NOT_IMPLEMENTED));
            }
            
            return $this->response(Http::_NOT_IMPLEMENTED);
        }
        
        if (!empty($matched_callback) && is_callable($matched_callback)) {
            // Run the before hook
            !empty($matched_hook_before) && is_callable($matched_hook_before) && @call_user_func($matched_hook_before, $args);

            // Run the call
            $called = call_user_func($matched_callback, $args);
            
            // Run the after hook
            !empty($matched_hook_after) && is_callable($matched_hook_after) && @call_user_func($matched_hook_after, $args + [ $called ]);

            if (is_null($called)) {
                return true;
            }
            
            return $called;
        }
        
        return true;
    } // dispatch

    // ~~~ PRIVATE ~~~

    /**
     * Converts a JSON into XML.
     *
     * @param object &$xml
     * @param array $data
     * @return xml
     */
    private function json2xml(&$xml, $data)
    {

    // exit when data is empty.
        if (is_null($data)) {
            return $xml;
        }

        // runs thru data to build xml
        foreach ($data as $dk => $dv) {

      // node data can be an array/ object
            if (is_array($dv)) {

        // is the key a string?
                if (!is_numeric($dk)) {

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
