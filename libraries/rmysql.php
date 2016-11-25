<?php 

 /* --------------------------------------------------------
 | Seed PHP - MySQLi Wrapper
 |
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.2
 | @license MIT
 | @see http://github.com/rogeriotaques/seed-php
 | @see http://github.com/rogeriotaques/rmysql
 * -------------------------------------------------------- */

namespace Libraries;

defined('ENV') or die('Direct script access is not allowed!');

class RMySQL {

  private $_host = 'localhost';
  private $_port = '3306';
  private $_user = 'root'; 
  private $_pass = ''; 
  private $_base = ''; 

  // the connection resource 
  private $_resource = null; 

  function __construct( $config = null ) {

    if (!is_null($config)) {
      if (!isset($config['host']) || !isset($config['port'])) {
        throw new \Exception('RMySQL: Config file missing "HOST" or "PORT" setting.');
      }

      if (!isset($config['user']) || !isset($config['pass'])) {
        throw new \Exception('RMySQL: Config file missing "USER" or "PASS" setting.');
      }

      if (!isset($config['base'])) {
        throw new \Exception('RMySQL: Config file missing "BASE" (database) setting.');
      }

      $this->setHost( $config['host'], $config['port'] ); 
      $this->setCredential( $config['user'], $config['pass'] ) or trigger_error('RMySQL: Config file missing "USER" or "PASS" setting.');
      $this->setDatabase( $config['base'] );
      
      return $this->connect();
    }

    return $this;

  } // __construct

  private function _escape ( $str = '' ) {
    if (!is_object( $this->_resource )) {
      return $str;
    } 

    return (
        is_null($str)
          ? 'NULL'
          : (
            is_numeric($str) || is_bool($str) 
              ? $str
              : (
                is_string($str)
                  ? "'{$this->_resource->real_escape_string($str)}'"
                  : $str 
              )
          )
      );
  } // _escape

  public function setHost ($host = 'localhost', $port = '3306', $charset = 'utf8') {
    if (!empty($host) && !is_null($host)) {
      $this->_host = $host;
    }

    if (!empty($port) && !is_null($port)) {
      $this->_port = $port;
    }

    if (!empty($charset) && !is_null($charset)) {
      $this->_charset = $charset;
    }

    return $this;
  } // setHost

  public function setPort ($port = '3306') {
    if (!empty($port) && !is_null($port)) {
      $this->_port = $port;
    }
    return $this;
  } // setPort

  public function setCredential ($user = 'root', $pass = '') {
    if (!empty($user) && !is_null($user)) {
      $this->_user = $user;
    }

    if (!empty($pass) && !is_null($pass)) {
      $this->_pass = $pass;
    }
    return $this;
  } // setCredential

  public function setDatabase ($name = '') {
    if (!empty($name) && !is_null($name)) {
      $this->_base = $name;
    }
    return $this;
  } // setDatabase

  public function setCharset ($charset = 'utf8') {
    $this->_charset = $charset;

    if (!is_null($this->_resource)) {
      $this->_resource->set_charset($charset);
    }

    return $this;
  } // setCharset

  public function connect () {
    if (empty($this->_base) || is_null($this->_base)) {
      throw new \Exception("RMySQL: Impossible to connect to an empty database name!");
    }

    $this->_resource = new \mysqli($this->_host, $this->_user, $this->_pass, $this->_base, $this->_port);
    
    if (!is_null($this->_resource->connect_error)) {
      throw new \Exception($this->_resource->connect_error, $this->_resource->connect_errno);
    }

    $this->_resource->set_charset($this->_charset);

    return $this;
  } // connect

  public function disconnect () {
    if (!is_object($this->_resource)) {
      return $this;
    }

    $this->_resource->close();
    $this->_resource = null;
    return $this;
  } // disconnect

  public function exec ( $query = '' ) {
    if (!is_object($this->_resource)) {
      throw new \Exception('RMySQL: Resource is missing!');
    }

    $res = $this->_resource->query( $query );
    $result = [];

    if ($res === false) {
      $error = mysqli_error_list($this->_resource);
      $error = array_shift($error);
      throw new \Exception($error['error'], $error['errno']);
    }

    if (preg_match('/^(\t|\r|\n|\s){0,}(select)/i', $query) > 0) {
      if ($res) {
        while( $row = $res->fetch_assoc() ) {
          $result[] = $row;
        }
      }

      return $result;
    }

    return $this->_resource->affected_rows;
  } // exec

  public function insert ($table = '', $data = []) {
    $self   = $this;
    $fields = array_keys($data); 
    $values = array_values($data); 

    $values = array_map(function ($el) use ($self) {
      return $self->_escape($el);
    }, $values);

    $stdin  = "INSERT INTO `{$table}` (`" . implode("`,`", $fields) . "`) VALUES (" . implode(",", $values) . ")";

    return $this->exec($stdin);
  } // insert 

  public function update ($table = '', $data = [], $where = null) {
    $self   = $this;
    $fields = array_keys($data); 
    $values = array_values($data); 

    $data = array_map(function ($k, $v, $i) use ($self) {
      $val = $self->_escape($v);
      $key = "`{$k}`";
      return "{$key} = {$val}";
    }, array_keys($data), array_values($data), array_keys(array_keys($data)));

    $stdin  = "UPDATE `{$table}` SET " . implode(",", $data);

    if (!is_null($where)) {
      $where = array_map(function ($k, $v, $i) use ($self) {
        $val = $self->_escape($v);
        $key = preg_match('/^(or|and)\s/i', $k) < 1 
          ? ($i > 0 ? "AND " : "") . "`{$k}`" 
          : preg_replace('/^(or|and)(\s)(.*)/i', '$1$2`$3`', $k);
        
        return "{$key} = {$val}";
      }, array_keys($where), array_values($where), array_keys(array_keys($where)));

      $stdin .= " WHERE " . implode('', $where);
    }

    return $this->exec($stdin);
  } // update 

  public function delete ($table = '', $where = []) {
    $self   = $this;
    $stdin  = "DELETE FROM `{$table}`";

    if (!is_null($where)) {
      $where = array_map(function ($k, $v, $i) use ($self) {
        $val = $self->_escape($v);
        $key = preg_match('/^(or|and)\s/i', $k) < 1 
          ? ($i > 0 ? "AND " : "") . "`{$k}`" 
          : preg_replace('/^(or|and)(\s)(.*)/i', '$1$2`$3`', $k);
        
        return "{$key} = {$val}";
      }, array_keys($where), array_values($where), array_keys(array_keys($where)));

      $stdin .= " WHERE " . implode('', $where);
    }
    
    return $this->exec($stdin);
  } // delete 

} // class