<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.7.8
 | @license MIT
 | @see http://github.com/abtzco/seed-php
 * -------------------------------------------------------- */

namespace Seed\Helper;

defined('SEED') or die('Direct script access is not allowed!');

class Curl {

	private $_ch;
	private $_timeout = 30;
	private $_headers = [];
	private $_options = [];
	private $_data = [];
	private $_url = '';
  private $_return_content_type = 'json';

  public $info = [];
  public $error;

  function __construct ( $url = '',  $options = [], $returnType = '' ) {
    if (!function_exists('curl_init')) {
      trigger_error('PHP was not built with --with-curl, rebuild PHP to use RCurl.') ;
    }

    // define a public error object
    $this->error = (object) ['code' => 0, 'message' => '', 'headers' => ''];

    if (!empty($returnType)) {
      $this->_return_content_type = $returnType;
    }

    if (!empty($url)) {
      $this->create( $url, $options );
    }

    if (count($options) > 0) {
      $this->_setOptions( $options );
    }
  }

  private function _reset () {
    $this->_options = [];
    $this->_data = [];
    $this->_headers = [];
    $this->info = [];
    $this->error->code = 0;
    $this->error->message = '';
    $this->error->headers = '';
  } // _reset

  private function _method ( $method = 'GET' ) {
    // set right method for the call
    $this->option(CURLOPT_CUSTOMREQUEST, $method);
  } // _method

  private function _setOptions ( $options = [] ) {
    foreach($options as $code => $value) {
      $this->option($code, $value);
    }

    return $this;
  } // _setOptions

  public function create ( $url = '', $options = [], $returnType = '' ) {
    $this->_reset(); // reset

    if (!empty($returnType)) {
      $this->_return_content_type = $returnType;
    }

    if (count($options) > 0) {
      $this->_options = $options;
    }

    if (!empty($url)) {
      // mat_ches a protocol, if any.
      if(!preg_match('!^\w+://! i', $url)) {
          $url = 'http://'.$url;
      }

      $this->_url = $url;
      $this->_ch = curl_init();
    }

    return $this;
  } // create

  public function data ( $data = [] ) {
    if (is_string($data) && empty($data)) {
      return $this;
    }

    if (is_array($data) && count($data) > 0 && is_array($this->_data)) {
      $this->_data = array_merge($this->_data, $data);
    } else {
      $this->_data = $data;
    }

    return $this;
  } // data

  public function option ( $code = false, $value = '' ) {
    if ($code !== false) {
      $this->_options[$code] = $value;
    }

    return $this;
  } // option

  public function proxy ( $url = '', $username = '', $password = '' ) {
    $this->option(CURLOPT_HTTPPROXYTUNNEL. TRUE);
    $this->option(CURLOPT_PROXY, $url);
    $this->option(CURLOPT_PROXYUSERPWD, $username . ':' . $password);
    return $this;
  } // proxy

  public function cookies ( $params = [] ) {
    if (is_array($params)) {
      $params = http_build_query($params);
    }

    $this->option(CURLOPT_COOKIE, $params);
    return $this;
  } // cookies

  public function credential ( $username = '', $password = '') {
    $this->option(CURLOPT_USERPWD, $username . ':' . $password);
    return $this;
  }  // credential

  public function header ( $header = '' ) {
		if (is_array($header)) {
      $this->_headers = array_merge($this->_headers, $header);
    }

    if (is_string($header) && !empty($header)) {
      $this->_headers[] = $header;
    }

    return $this;
	} // header

  public function get ( $url = '', $options = [] ) {
    return $this->run('GET', $url, $options);
  } // get

  public function post( $url = '', $options = [] ) {
    return $this->run('POST', $url, $options);
  } // post

  public function put( $url = '', $options = [] ) {
    return $this->run('PUT', $url, $options);
  } // put

  public function update( $url = '', $options = [] ) {
    return $this->run('UPDATE', $url, $options);
  } // update

  public function delete( $url = '', $options = [] ) {
    return $this->run('DELETE', $url, $options);
  } // update

  /**
   * @method string [$method] - Can be any http method, such as POST, PUT, GET, DELETE, etc.
   */
  public function run( $method = 'GET', $url = '', $options = [] ) {
    // if an url is given here, creates a new session ...
    if (!empty($url)) {
      $this->create($url);
    }

    // additional options
    if (strtolower(trim($method)) === 'post') {
      $this->option(CURLOPT_POST, true);
    }

    $this->_setOptions( $options );
    $this->_method( strtoupper(trim($method)) );

    return $this->execute();
  } // run

  public function execute() {
    // set default options, if not there
    if(!isset($this->_options[CURLOPT_TIMEOUT])) $this->option(CURLOPT_TIMEOUT, $this->_timeout);
    if(!isset($this->_options[CURLOPT_RETURNTRANSFER])) $this->option(CURLOPT_RETURNTRANSFER, true);
    if(!isset($this->_options[CURLOPT_FOLLOWLOCATION])) $this->option(CURLOPT_FOLLOWLOCATION, true);
    if(!isset($this->_options[CURLOPT_FAILONERROR])) $this->option(CURLOPT_FAILONERROR, true);
    if(!isset($this->_options[CURLOPT_SSL_VERIFYPEER])) $this->option(CURLOPT_SSL_VERIFYPEER, false);
    if(!isset($this->_options[CURLOPT_FRESH_CONNECT])) $this->option(CURLOPT_FRESH_CONNECT, true);
    if(!isset($this->_options[CURLOPT_VERBOSE])) $this->option(CURLOPT_VERBOSE, true);
    if(!isset($this->_options[CURLINFO_HEADER_OUT])) $this->option(CURLINFO_HEADER_OUT, true);

    // set url, if any
    if (!empty($this->_url)) {
      $this->option(CURLOPT_URL, $this->_url);
    }

    // set headers, if any
    if (count($this->_headers) > 0) {
      $this->option(CURLOPT_HTTPHEADER, $this->_headers);
    }

    // set data, if any
    if ((is_array($this->_data) && count($this->_data) > 0) || (is_string($this->_data) && !empty($this->_data))) {
      $this->option(CURLOPT_POSTFIELDS, $this->_data);
    }

    // var_dump($this->_options);

    // set all options to the curl session,
    // which includes the chosen method.
    curl_setopt_array($this->_ch, $this->_options);

    // run ...
    $result = curl_exec($this->_ch);

    // grab execution info
    $this->info = curl_getinfo($this->_ch);

    // whenever it fails, grab error info
    if ($result === false) {
      $this->error->code = (isset($this->info['http_code']) ? $this->info['http_code'] : curl_errno($this->_ch));
      $this->error->message = curl_error($this->_ch);
      $this->error->headers = isset($this->info['request_header']) ? $this->info['request_header'] : [];
    } else {
      // check what kind result is expected.
      // whenever it's json (default), encodes it
      switch ($this->_return_content_type) {

        case 'json':
          if (is_string($result) && !empty($result)) {
            $orig   = $result;
            $result = json_decode($result);

            // after trying decode a json content and for some reason
            // it doesn't work, then, falls back to the original result.
            if ( is_null($result) ) {
              $result = $orig;
            }
          }

          break;
      }
    }

    // close connection
    curl_close($this->_ch);
    $this->_ch = null;

    return $result;
  } // execute

} // class
