<?php 

 /* --------------------------------------------------------
 | Seed-PHP Microframework
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1.5
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 * -------------------------------------------------------- */

namespace Seed\Helper;

defined('SEED') or die('Direct script access is not allowed!');

class Logger {
  private $_db;
  private $_data = [];
  private $_config;
  private $_table = 'log_usage';

  function __construct ( $cfg = [] ) {
    $this->_config = $cfg;
    $this->_db = new \Seed\Helper\MySQL($this->_config);
  } // __construct

  public function endpoint ( $str = '' ) {
    $this->_data['endpoint'] = $str;
    return $this;
  } // endpoint

  public function resource ( $str = '' ) {
    $this->_data['resource'] = $str;
    return $this;
  } // resource

  public function requestIP ( $str = '' ) {
    $this->_data['request_ip'] = $str;
    return $this;
  } // requestIP

  public function requestHeader ( $data = [] ) {
    $this->_data['request_header'] = json_encode($data);
    return $this;
  } // requestHeader

  public function requestData ( $data = [] ) {
    $this->_data['request_data'] = json_encode($data);
    return $this;
  } // requestData

  public function responseData ( $data = [] ) {
    $this->_data['response_data'] = json_encode($data);
    return $this;
  } // responseData

  public function responseCode ( $code = '' ) {
    $this->_data['response_code'] = $code;
    return $this;
  } // responseCode

  public function log () {
    $res = false;

    try {
      $res = $this->_db->insert($this->_table, $this->_data);
    } catch (\Exception $e) {
      // result is already set to false ... 
    }

    return $res;
  }

} // class
