<?php

/**
 * Seed-PHP Microframework.
 * @author Rogerio Taques
 * @license MIT
 * @see http://github.com/rogeriotaques/seed-php
 */

namespace SeedPHP;

use SeedPHP\Router;
use SeedPHP\Helper\Http;

class Core extends Router
{
    /** @var object an object containing the request data */
    private $_request;

    /** @var object */
    private static $instance;

    // ~~~ MAGIC METHODS ~~~

    /**
     * The constructor.
     */
    public function __construct()
    {
        // Nothing to construct
    }

    // ~~~ PUBLIC METHODS ~~~

    /**
     * Returns the singleton instance.
     *
     * @return Core
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Core();
        }

        return self::$instance;
    } // getInstance

    /**
     * Starts the working flow.
     *
     * @return bool
     */
    public function run()
    {
        $this->setPageHeaders();
        $this->buildRequest();

        // is it an OPTIONS request?
        // it's used to confirm if app accept CORS calls
        if ($this->_method === 'OPTIONS') {
            return $this->response(Http::_OK); // do accept it
        }

        try {
            return $this->dispatch($this->_request->args);
        } catch (\Throwable $th1) {
            $_status = null;

            // Get the most appropriated HTTP status code.
            try {
                $_status = Http::getHTTPStatus($th1->getCode());
            } catch (\Throwable $th2) {
                $_status = Http::getHTTPStatus(500);
            }

            // Always an error handler was set, use it.
            if ($this->_error_handler !== false) {
                return call_user_func($this->_error_handler, (object) $_status);
            }

            return $this->response(
                $_status['code'],
                [ "message" => $th1->getMessage() ]
            );
        }
    } // run

    /**
     * Retrieve the request headers.
     * When $key is given, returns a string or false when the key is not found.
     *
     * @param [string] $key
     * @return array<string>|string|false
     */
    public function header($key = null)
    {
        $headers = getallheaders();

        if ($key === null) {
            return $headers;
        }

        // Tries the header case-sensitive
        if (isset($headers[$key]) !== false) {
            return $headers[$key];
        }

        // Tries the header case-insensitive
        // @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
        if (isset($headers[strtolower($key)]) !== false) {
            return $headers[strtolower($key)];
        }

        // Otherwise, error
        return false;
    } // header

    /**
     * Retrieve posted data.
     * When $key is given, returns a string or false when the key is not found.
     *
     * @param [string] $key
     * @return array<string>|string|false
     */
    public function post($key = null)
    {
        // The 'always_populate_raw_post_data' is deprecated since php@5.6.
        // So, check if auto_populate_post_data is enabled, if not enabled, use php://input instead.
        $post_data = [];

        if ((!isset($_POST) || count($_POST) === 0) &&
      intval(ini_get('always_populate_raw_post_data')) < 1) {
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

        return (isset($post_data[$key]) ? $post_data[$key] : false);
    } // post

    /**
     * Retrieve data passed as query-string.
     * When $key is given, returns a string or false when the key is not found.
     *
     * @param [string] $key
     * @return array<string>|string|false
     */
    public function get($key = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return (isset($_GET[$key]) ? $_GET[$key] : false);
    } // get

    /**
     * Retrieve submited files.
     * When $key is given, returns a string or false when the key is not found.
     *
     * @param [string] $key
     * @return array<file>|file|false
     */
    public function file($key = null)
    {
        if ($key === null) {
            return $_FILES;
        }

        return (isset($_FILES[$key]) ? $_FILES[$key] : false);
    } // file

    /**
     * Alias for file().
     * @param string [$key]
     * @return string|array
     */
    public function files($key = null)
    {
        return $this->file($key);
    } // files

    public function cookie($key = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : false);
    } // cookie

    /**
     * Retrieve data passed in the put method.
     * When $key is given, returns a string or false when the key is not found.
     *
     * @param [string] $key
     * @return array<string>|string|false
     */
    public function put($key = null)
    {
        $php_input = file_get_contents("php://input");

        // Try parsing it from JSON
        $_PUT = json_decode($php_input, true);

        if (!$_PUT) {
            // Fallback to parsing it from URL encoded string
            parse_str($php_input, $_PUT);
        }

        if ($key === null) {
            return $_PUT;
        }

        return (isset($_PUT[$key]) ? $_PUT[$key] : false);
    } // put

    /**
     * Return the request object.
     *
     * @return object
     */
    public function request()
    {
        return $this->_request;
    }

    /**
     * Load helpers and make them available through the App instance.
     *
     * @param string $component
     * @param array  $config
     * @param string $alias
     * @return Core
     */
    public function load($component = '', $config = [], $alias = ''): Core
    {
        if (empty($component)) {
            return false;
        }

        if (strtolower($component) === 'router') {
            parent::readRoutesFrom($config[ 'path' ] ?? '');
            return $this;
        }

        $class = "\\SeedPHP\\Helper\\" . $this->camelfy($component);
        $alias = (!empty($alias) && is_string($alias) ? $alias : $component);

        $this->$alias = new $class($config);

        if ($component !== $alias) {
            $this->$component = $alias; // register the name chosen as alias in the original helper
        }

        return $this;
    } // load

    // ~~~ PRIVATE METHODS ~~~

    /**
     * Return an URL safe string.
     *
     * @param [type] $str
     * @param boolean $first_lower
     * @return string
     */
    private function camelfy($str, $first_lower = false)
    {
        if (empty($str)) {
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
     * Write the page headers (used before returning a response).
     *
     * @return void
     */
    private function setPageHeaders()
    {
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
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->_cache_max_age) . ' GMT');
        }

        // when cache is not allowed, adds necessary headers to force browser ignore caching
        else {
            header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: Mon, 1 Jan 1900 05:00:00 GMT");
        }
    } // setPageHeaders

    /**
     * Build the request object which will be returned with $this->request().
     *
     * @return void
     */
    private function buildRequest()
    {
        // Define base path
        $patt = str_replace(array('\\', ' '), array('/', '%20'), dirname($_SERVER['SCRIPT_NAME']));

        // Retrieve requested uri
        $this->_uri = isset($_SERVER['REQUEST_URI'])
          ? $_SERVER['REQUEST_URI']
          : '/';

        // Remove query-string (if any)
        if (strpos($this->_uri, '?') !== false) {
            $this->_uri = substr($this->_uri, 0, strpos($this->_uri, '?'));
        }

        // Remove base path from uri
        $this->_uri = preg_replace('@^' . $patt . '@', '', $this->_uri);

        // Identify the request method
        $this->_method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');

        // Remove trailing slashes to easily match paths.
        // It must be done before adding a "root" slash, otherwise it breaks the router for (hidden) index pages.
        $this->_uri = preg_replace('/\/$/', '', $this->_uri);

        // Add a initial (root) slash case it's not present
        if (!preg_match('/^\//', $this->_uri)) {
            $this->_uri = "/{$this->_uri}";
        }

        // Explode arguments
        $args = explode('/', $this->_uri);

        // Filter empty arguments
        // If there's empty argument, it means the uri has finished in a slash
        $args = array_filter($args, function ($el) {
            return !empty($el) && !is_null($el);
        });

        // Has args only one element and is it empty?
        // Means it's the start point (root)
        if (count($args) === 1 && empty($args[0])) {
            $args[0] = '/';
        }

        // Extracts uri parts
        $endpoint = array_shift($args);
        $verb     = array_shift($args);
        $id       = count($args) ? array_shift($args) : null;

        // Is verb an ID?
        if (is_numeric($verb)) {
            $_verb = $id;
            $id    = $verb;
            $verb  = $_verb;
        }

        // Do arguments can be set in pairs?
        if (count($args) % 2 === 0) {
            // Fetches keys from args
            $args_keys = array_filter($args, function ($k) use (&$args) {
                $k = key($args);
                next($args);
                return !($k & 1);
            });

            // Fetches values from args
            $args_values = array_filter($args, function ($k) use (&$args) {
                $k = key($args);
                next($args);
                return $k & 1;
            });

            // Avoid matching in pairs when ID is fetched in the args.
            // This prevents unexpected behaviors and values in the args array
            // and also keeps the backward compatibility with previous implementations.
            foreach ($args_keys as $k => $v) {
                if (is_numeric($v)) {
                    unset($args_keys[$k], $args_values[$k]);
                }
            }

            $args_combined = [];

            if (count($args_keys) === count($args_values)) {
                $args_combined = @array_combine($args_keys, $args_values);
            }

            // Makes it an assossiative array
            $args = array_merge($args, $args_combined);
        }

        // Finally returns
        $this->_request = (object)[
            'base' => Http::getBaseUrl(),
            'method' => $this->_method,
            'endpoint' => $endpoint,
            'verb' => $verb,
            'id' => $id,
            'args' => $args
        ];
    } // buildRequest
} // class
