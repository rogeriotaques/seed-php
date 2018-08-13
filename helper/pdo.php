<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework
 | @author Rogerio Taques (hello@abtz.co)
 | @version 1.0.0
 | @license MIT
 | @see http://github.com/abtzco/seed-php
 * -------------------------------------------------------- */

namespace SeedPHP\Helper;

class PDO
{
  private $_dns = '';
  private $_driver = 'mysql';
  private $_host = 'localhost';
  private $_port = '3306';
  private $_user = 'root';
  private $_pass = '';
  private $_base = 'test';
  private $_charset = 'utf8';
  private $_last_result_count = 0;

  private static $_resource = null; // the connection resource
  public static $_attempts = 0;

  function __construct(array $config = null)
  {
    if (!empty($config)) {
      if (isset($config['host'])) {
        $this->_host = $config['host'];
      }

      if (isset($config['port'])) {
        $this->_port = $config['port'];
      }

      if (isset($config['base'])) {
        $this->_base = $config['base'];
      }

      if (isset($config['charset'])) {
        $this->_charset = $config['charset'];
      }

      if (isset($config['user']) && isset($config['pass'])) {
        $this->setCredential($config['user'], $config['pass']);
      }
    }
  } // __construct

  public function __set($name, $value)
  {
    // Prevents unexpected sets
  }

  public function __get($name)
  {
    // Prevents unexpected gets
  }

  public function setHost($host = 'localhost', $port = '3306', $charset = 'utf8')
  {
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

  public function setPort($port = '3306')
  {
    if (!empty($port) && !is_null($port)) {
      $this->_port = $port;
    }
    return $this;
  } // setPort

  public function setCredential($user = 'root', $pass = '')
  {
    if (!empty($user) && !is_null($user)) {
      $this->_user = $user;
    }

    if (!empty($pass) && !is_null($pass)) {
      $this->_pass = $pass;
    }

    return $this;
  } // setCredential

  public function setDatabase($name = '')
  {
    if (!empty($name) && !is_null($name)) {
      $this->_base = $name;
    }
    return $this;
  } // setDatabase

  public function setCharset($charset = 'utf8')
  {
    $this->_charset = $charset;

    if (!is_null(self::$_resource)) {
      self::$_resource->set_charset($charset);
    }

    return $this;
  } // setCharset

  public function connect()
  {
    // Allows chained calls.
    if (is_object(self::$_resource)) {
      self::$_attempts += 1;
      return $this;
    }

    $this->_dns = "{$this->_driver}:host={$this->_host};port={$this->_port};dbname={$this->_base};charset={$this->_charset}";
    self::$_resource = new \PDO($this->_dns, $this->_user, $this->_pass);

    // if (!is_null(self::$_resource->connect_error)) {
    //   throw new \Exception(self::$_resource->connect_error, self::$_resource->connect_errno);
    // }

    self::$_attempts += 1;

    echo "Connected <br>\n";

    return $this;
  } // connect

  public function disconnect()
  {
    // If there was chained connections, 
    // just decreases the connection attempts count.
    if (self::$_attempts > 1) {
      self::$_attempts -= 1;
      return $this;
    }

    if (!is_object(self::$_resource)) {
      return $this;
    }

    self::$_resource = null;

    echo "Disconnected <br>\n";

    return $this;
  } // disconnect

  /**
   * Executes an SQL statement.
   * @param string $query
   * @return integer|array<array>
   * @throws Exception
   */
  public function exec($query = '', array $values = null)
  {
    if (!is_object(self::$_resource)) {
      throw new \Exception('Seed-PHP MySQL: Cannot run this query, resource is missing!');
    }

    // Prepare the statement to be executed, removing
    // unnecessary spaces and breaklines.
    $query = trim($query);
    $query = preg_replace('/(\n|\r)/', ' ', $query);
    $query = preg_replace('/\s{2,}/', ' ', $query);

    try {
      if (empty($values)) {
        $stmt = self::$_resource->query($query);
      } else {
        $stmt = self::$_resource->prepare($query);
        $stmt->execute($values);
      }
    } catch (\PDOException $pdoex) {
      throw new \Exception($pdoex->getMessage(), $pdoex->getCode());
    }

    $result = [];

    if (preg_match('/^(\t|\r|\n|\s){0,}(select)/i', $query) > 0) {
      if ($stmt && !is_bool($stmt)) {
        $result[] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      }

      // Provide the result count for a select statement
      $this->_last_result_count = !is_bool($stmt)
        ? $stmt->rowCount()
        : 0;

      return $result;
    }

    // When not a select, result count is zero.
    $this->_last_result_count = 0;

    // Returns the number of affected rows
    return "{$stmt}";
  } // exec

  public function insert($table = '', $data = [])
  {
    $self = $this;
    $placeholders = [];
    $fields = array_keys($data);
    $values = array_values($data);

    foreach ($fields as $f) {
      $placeholders[] = '?';
    }

    $stdin = "
      INSERT INTO `{$table}` (`" . implode("`,`", $fields) . "`) 
      VALUES (" . implode(",", $placeholders) . ")
    ";

    return $this->exec($stdin, $values);
  } // insert

  public function update($table = '', $data = [], $where = null)
  {
    $self = $this;
    $fields = array_keys($data);
    $values = array_values($data);

    $data = array_map(function ($k) {
      return "`{$k}` = ?";
    }, array_keys($data));

    $stdin = "UPDATE `{$table}` SET " . implode(",", $data);

    if (!is_null($where)) {
      $where = array_map(function ($k, $i) use ($self) {
        $key = preg_match('/^(or|and)\s/i', $k) < 1
          ? ($i > 0 ? " AND " : " ") . "`{$k}`"
          : preg_replace('/^(or|and)(\s)(.*)/i', '$1$2`$3`', $k);

        return "{$key} = ?";
      }, array_keys($where), array_keys(array_keys($where)));

      $stdin .= " WHERE " . implode('', $where);
    }

    return $this->exec($stdin, $values);
  } // update

  public function delete($table = '', $where = [])
  {
    $self = $this;
    $stdin = "DELETE FROM `{$table}`";
    $values = !is_null($where) ? array_values($where) : null;

    if (!is_null($where)) {
      $where = array_map(function ($k, $i) use ($self) {
        $key = preg_match('/^(or|and)\s/i', $k) < 1
          ? ($i > 0 ? " AND " : " ") . "`{$k}`"
          : preg_replace('/^(or|and)(\s)(.*)/i', '$1$2`$3`', $k);

        return "{$key} = ?";
      }, array_keys($where), array_keys(array_keys($where)));

      $stdin .= " WHERE " . implode('', $where);
    }

    if (!empty($values)) {
      return $this->exec($stdin, $values);
    }

    return $this->exec($stdin);
  } // delete
} // PDO
