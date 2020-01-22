<?php

/** 
 * Seed-PHP Microframework
 * @copyright Abtz Labs
 * @license MIT
 * @see http://github.com/abtzco/seed-php
 */

namespace SeedPHP\Helper;

use PDO;
use PDOException;
use SeedPHP\Helper\Http;

/**
 * The Database (PDO) helper
 */
class Database
{
    /** @var string */
    private $_dns = '';

    /** @var string - defaults to 'mysql' */
    private $_driver = 'mysql';

    /** @var string - defaults to 'localhost' */
    private $_host = 'localhost';

    /** @var string - defaults to '3306' */
    private $_port = '3306';

    /** @var string - defaults to 'root' */
    private $_user = 'root';

    /** @var string - defaults to empty */
    private $_pass = '';

    /** @var string - defaults to 'test' */
    private $_base = 'test';

    /** @var string - defaults to 'utf8mb4' */
    private $_charset = 'utf8mb4';

    /** @var integer - defaults to 0 */
    private $_last_result_count = 0;

    /** @var object - the connection resource. defaults to null */
    private static $_resource = null; 

    /** @var integer - how many transactions in chain. defaults to 0 */
    private static $_transactions = 0;

    /** @var integer - how many connections in chain. defaults to 0 */
    public static $_attempts = 0;

    /**
     * Constructs a new PDO helper
     */
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

    /**
     * Prevents sets not using the proper set... methods
     */
    public function __set($name, $value)
    {
        // Prevents unexpected sets
    }

    /**
     * Prevents gets not using the proper get... methods
     */
    public function __get($name)
    {
        // Prevents unexpected gets
    }

    /**
     * Sets the database host address
     * @param string $host Default to localhost
     * @param string $port Default to 3306
     * @param string $charset Default to utf8mb4
     * @return SeedPHP\Helper\Database
     */
    public function setHost($host = 'localhost', $port = '3306', $charset = 'utf8mb4')
    {
        if (!empty($host) && !is_null($host)) {
            $this->_host = "{$host}";
        }

        if (!empty($port) && !is_null($port)) {
            $this->_port = "{$port}";
        }

        if (!empty($charset) && !is_null($charset)) {
            $this->_charset = "{$charset}";
        }

        return $this;
    } // setHost

    /**
     * Sets the database connection port
     * @param string $port Default to 3306
     * @return SeedPHP\Helper\Database
     */
    public function setPort($port = '3306')
    {
        if (!empty($port) && !is_null($port)) {
            $this->_port = "{$port}";
        }

        return $this;
    } // setPort

    /**
     * Sets the database connection credential
     * @param string $user Default to root
     * @param string $password Default to empty
     * @return SeedPHP\Helper\Database
     */
    public function setCredential($user = 'root', $pass = '')
    {
        if (!empty($user) && !is_null($user)) {
            $this->_user = "{$user}";
        }

        if (!empty($pass) && !is_null($pass)) {
            $this->_pass = "{$pass}";
        }

        return $this;
    } // setCredential

    /**
     * Sets the database name
     * @param string $name Default to empty
     * @return SeedPHP\Helper\Database
     */
    public function setDatabase($name = '')
    {
        if (!empty($name) && !is_null($name)) {
            $this->_base = "{$name}";
        }

        return $this;
    } // setDatabase

    /**
     * Sets the database connection charset
     * @param string $charset Default to utf8
     * @return SeedPHP\Helper\Database
     */
    public function setCharset($charset = 'utf8mb4')
    {
        if (!empty($charset)) {
            $this->_charset = "{$charset}";
        }

        if (!is_null(self::$_resource) && $this->_driver === 'mysql') {
            self::$_resource->exec("set names {$this->_charset}");
        }

        return $this;
    } // setCharset

    /**
     * Attempt to connect to the set database.
     * @return SeedPHP\Helper\Database
     * @throws PDOExpception
     */
    public function connect()
    {
        // Allows chained calls.
        if (is_object(self::$_resource)) {
            self::$_attempts += 1;
            return $this;
        }

        try {
            $this->_dns = "{$this->_driver}:host={$this->_host};port={$this->_port};dbname={$this->_base};charset={$this->_charset}";
            self::$_resource = new PDO($this->_dns, $this->_user, $this->_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            if (!is_null(self::$_resource) && $this->_driver === 'mysql') {
                self::$_resource->exec("set names {$this->_charset}");
            }

            self::$_attempts += 1;
        } catch (\PDOException $PDOEx) {
            $error_code = $PDOEx->getCode();

            throw new PDOException(
                "SeedPHP\Helper\Database::connect : " . $PDOEx->getMessage(), 
                is_numeric($error_code) ? $error_code : Http::_INTERNAL_SERVER_ERROR
            );
        }

        return $this;
    } // connect

    /**
     * Attempts to disconnect from an already connected database.
     * @return SeedPHP\Helper\Database
     */
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

        return $this;
    } // disconnect

    /**
     * Executes an SQL statement. Supports statement variables binding.
     * @param string $query
     * @param array $values Values to bind in the query. Default to null.
     * @return integer|array<array>
     * @throws ErrorException|PDOException
     */
    public function exec($query = '', array $values = null)
    {
        if (!is_object(self::$_resource)) {
            throw new \ErrorException(
                'SeedPHP\Helper\Database::exec : Cannot run this query, resource is missing!',
                Http::_BAD_REQUEST
            );
        }
    
        if (!is_string($query) || empty($query)) {
            throw new \ErrorException(
                'SeedPHP\Helper\Database::exec : Query cannot be empty and must be a string!',
                Http::_BAD_REQUEST
            );
        }

        // Prepare the statement to be executed, removing
        // unnecessary spaces and breaklines.
        $query = trim($query);
        $query = preg_replace('/(\n|\r)/', ' ', $query);

        try {
            if (empty($values)) {
                $stmt = self::$_resource->query($query);
            } else {
                $stmt = self::$_resource->prepare($query);
                $stmt->execute($values);
            }
        } catch (\PDOException $PDOEx) {
            $error_code = $PDOEx->getCode();

            throw new \PDOException(
                "SeedPHP\Helper\Database::exec : " . $PDOEx->getMessage(), 
                is_numeric($error_code) ? $error_code : Http::_INTERNAL_SERVER_ERROR
            );
        }

        $result = [];

        if (preg_match('/^(\t|\r|\n|\s){0,}(select)/i', $query) > 0) {
            if ($stmt && !is_bool($stmt)) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Provide the result count for a select statement
            $this->_last_result_count = !is_bool($stmt)
                ? $stmt->rowCount()
                : 0;

            return $result;
        }

        // When not a select, result count is zero.
        $this->_last_result_count = 0;

        // Returns 1 by default for non selects
        return 1;
    } // exec

    /**
     * Manage transactions.
     * @param string $status Possible values: begin, commit or rollback
     * @return SeedPHP\Helper\Database
     */
    public function transaction($status = 'begin')
    {
        switch ($status) {
            case 'commit':
                if (self::$_transactions > 0) {
                    self::$_transactions -= 1;
                    self::$_resource->commit();
                    self::$_resource->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
                }
                break;

            case 'rollback':
                if (self::$_transactions > 1) {
                    self::$_resource->execute('rollback to trans' . (self::$_transactions + 1));
                } else {
                    self::$_resource->rollback();
                    self::$_resource->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
                }

                break;

            // Any status other than commit and rollback will be understood as 'begin'
            default:
                self::$_transactions += 1;
                self::$_resource->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
                self::$_resource->beginTransaction();

                break;
        }

        return $this;
    } // transaction

    /**
     * Insert new records into given table.
     * @param string $table
     * @param array $data 
     * @return integer|boolean
     * @throws ErrorException
     */
    public function insert($table = '', array $data = null)
    {
        if (!is_string($table) || empty($table)) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::insert : Insert : Invalid table name",
                Http::_BAD_REQUEST
            );
        }

        if (empty($data)) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::insert : Insert : Data cannot be empty",
                Http::_BAD_REQUEST
            );
        }

        $columns = array_keys($data);
        $values  = array_values($data);
        $placeholders = array_pad([], count($values), '?');

        $table = $this->_escapeTableName($table);
        $columns = array_map(array($this, '_escapeColumnName'), $columns);

        $stdin = "
        INSERT INTO {$table} (" . implode(", ", $columns) . ") 
        VALUES (" . implode(", ", $placeholders) . ")
        ";

        return $this->exec($stdin, $values);
    } // insert

    /**
     * Updates records from given table.
     * @param string $table 
     * @param array $data 
     * @param array [$where] Optional. Default to null
     * @return integer|boolean
     * @throws ErrorException
     */
    public function update($table = '', array $data = [], array $where = null)
    {
        if (!is_string($table) || empty($table)) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::update : Invalid table name", 
                Http::_BAD_REQUEST
            );
        }

        if (empty($data)) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::update : Data cannot be empty", 
                Http::_BAD_REQUEST
            );
        }

        $self   = $this;
        $fields = array_keys($data);
        $values = array_values($data);
        $where_values = !empty($where) ? array_values($where) : [];

        $data = array_map(function ($k) use ($self) {
            return $self->_escapeColumnName($k) . " = ?";
        }, $fields);

        $table = $this->_escapeTableName($table);
        $stdin = "UPDATE {$table} SET " . implode(", ", $data);

        if (!empty($where)) {
            $where = $this->_args2string($where);
            $stdin .= " {$where} ";
        }

        if (!empty($where_values)) {
            $values = array_merge($values, $where_values);
        }

        return $this->exec($stdin, $values);
    } // update

    /**
     * Deletes records from given table.
     * @param string $table 
     * @param array [$where] Optional. Default to null 
     * @return integer|boolean
     * @throws ErrorException
     */
    public function delete($table = '', array $where = null)
    {
        if (!is_string($table) || empty($table)) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::delete : Invalid table name",
                Http::_BAD_REQUEST
            );
        }

        $table  = $this->_escapeTableName($table);
        $stdin  = "DELETE FROM {$table} ";
        $values = !empty($where) ? array_values($where) : null;

        if (!empty($where)) {
            $where = $this->_args2string($where);
            $stdin .= " {$where} ";
        }

        if (!empty($values)) {
            return $this->exec($stdin, $values);
        }

        return $this->exec($stdin);
    } // delete

    /**
     * Fetches a recordset from the given table.
     *
     * @param string    $table
     * @param array     [$cols]     Optional. Default to *
     * @param array     [$where]    Optional. An array of key-value defining the where condition.
     * @param integer   [$limit]    Optional. Default to 1000
     * @param integer   [$offset]   Optional. Default to 0
     * @param array     [$order]    Optional. Default to `id` ASC
     * @param array     [$joins]    Optional. List of joining tables. Default to empty. E.g [ 'table_1 t1', 'table_2 t2', ... , 'table_N tN' ]
     * @return array<array>
     */
    public function fetch(string $table = '', array $cols = ['*'], array $where = [], int $limit = 1000, int $offset = 0, array $order = ['id' => 'ASC'], array $joins = [])
    {
        if (!is_string($table) || empty($table)) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::fetch : Invalid table name", 
                Http::_BAD_REQUEST
            );
        }

        if (!is_numeric($limit) || intval($limit) < 0) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::fetch : Invalid limit", 
                Http::_BAD_REQUEST
            );
        }

        if (!is_numeric($offset) || intval($offset) < 0) {
            throw new \ErrorException(
                "SeedPHP\Helper\Database::fetch : Invalid limit", 
                Http::_BAD_REQUEST
            );
        }

        // Normalize the columns
        if (!empty($cols)) {
            $cols = array_map(array($this, '_escapeColumnName'), $cols);
            $cols = implode(", ", $cols);
        }

        // Normalize the table name
        $table = $this->_escapeTableName($table);
        $where_values = null;

        // Query
        $sql = "SELECT {$cols} FROM {$table}";

        // Append joined tables
        if (!empty($joins)) {
            $joins = array_map(array($this, '_escapeTableName'), $joins);
            $sql .= ", " . implode(", ", $joins);
        }

        if (!empty($where)) {
            $where_values = array_values($where);
            $where_values = array_filter($where_values, function ($val) {
                return $val !== null;
            });

            $where = $this->_args2string($where);
            
            $sql .= " {$where}";
        }

        if (!empty($order)) {
            $order = array_map(function ($col, $sort) {
                return "`{$col}` {$sort}";
            }, array_keys($order), array_values($order));

            $order = implode(", ", $order);
            $sql .= " ORDER BY {$order}";
        }

        if ($limit) {
            $sql .= " LIMIT " . ($offset ? "{$offset}, " : '') . "{$limit}";
        }

        // Execute
        $res = $this->exec($sql, $where_values);

        return $res;
    } // fetch

    /**
     * Returns the connection resource link.
     * @return MySQLConnectionObject
     */
    public function getLink()
    {
        return self::$_resource;
    } // getLink

    /**
     * Returns the latest inserted ID
     * @return integer
     */
    public function insertedId()
    {
        return self::$_resource->lastInsertId();
    } // insertedId

    /**
     * Returns the result count after a query.
     * @return integer
     */
    public function resultCount()
    {
        return $this->_last_result_count;
    } // insertedId

    /**
     * Stringify given arguments into SQL where statement
     *
     * @param array $args
     * @return string
     */
    private function _args2string($args = [])
    {
        if (is_null($args) || count($args) === 0) {
            return false;
        }

        // Counts the arguments
        $arg_count = 0;

        // Prepare args to the statement
        foreach ($args as $k => $val) {
            if ($val === null) {
                $args[$k] = $k;
            } else {
                if (preg_match('/\s(=|>=|<=|<>|\*=|is|is not|between|in|not in)\s?$/i', $k) === 0) {
                    $args[$k] = $this->_escapeColumnName($k) . " = ?";
                } else {
                    $args[$k] = "{$k} ?";
                }
            }

            if ($arg_count > 0 && preg_match('/^(and|or)\s/i', $args[$k]) === 0) {
                $args[$k] = " AND {$args[$k]}";
            } else {
                $args[$k] = ($arg_count > 0 ? " " : "") . "{$args[$k]}";
            }

            $arg_count += 1;
        }

        return 'WHERE ' . implode(' ', $args);
    } // _args2string

    /**
     * Escape and normalize tables' names.
     *
     * @param string $table
     * @return string
     */
    private function _escapeTableName($table = '')
    {
        $_table = trim(strtolower($table));

        if (strpos($_table, ' ') !== false) {
            $_table = strpos($_table, ' as ') ? explode(' as ', $_table) : explode(' ', $_table);
            $_table = "`" . implode("` AS `", $_table) . "`";
        } else {
            $_table = "`{$_table}`";
        }

        return $_table;
    } // _escapeTableName

    /**
     * Escape and normalize columns' names.
     *
     * @param string $column
     * @return string
     */
    private function _escapeColumnName($column = '')
    {
        // Wildcard?
        if ($column == '*') {
            return $column;
        }

        // Does it contain subqueries?
        // Subqueries cannot be escaped for now!
        if (strpos($column, "(") !== false) {
            return $column;
        }
        
        $_col = trim(strtolower($column));
        $_talias = "";

        // Is column using table alias?
        if (strpos($_col, '.') !== false) {
            // Escape table alias
            list($_talias, $_col) = explode(".", $_col);
            $_talias = "`{$_talias}`.";
        }

        // Is column using an alias?
        if (strpos($_col, " as ") !== false) {
            // Escape table alias
            $_col = explode(" as ", $_col);
            $_col = "`" . implode("` AS `", $_col) . "`";
        } else {
            $_col = "`{$_col}`";
        }

        return "{$_talias}{$_col}";
    } // _escapeColumnName
} // Database
