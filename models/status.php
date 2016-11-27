<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 * -------------------------------------------------------- */

namespace Models;

defined('ENV') or die('Direct script access is not allowed!');

use Seed\Model;

class Status extends Model {

  public function getTotalQueries () {
    $this->db->connect();
    
    $res = $this->db->exec('select count(*) as total from log_usage');
    $res = array_shift($res);

    $this->db->disconnect();

    return $res['total'];
  } // getTotalQueries;

  public function getQueriesPerDay () {
    $this->db->connect();
    
    $res = $this->db->exec('
      select avg(total) as counter from (
        select count(*) as total from log_usage group by unix_timestamp(`timestamp`) div 86400
      ) as A
    ');
    $res = array_shift($res);

    $this->db->disconnect();

    return intval($res['counter']);
  } // getQueriesPerDay

  public function getQueriesPerSecond () {
    $this->db->connect();

    // retrieve total served queries per second
    $res = $this->db->exec('
      select avg(total) as average from (
        select count(*) as total from log_usage group by unix_timestamp(`timestamp`) div 1
      ) as A
    ');
    $res = array_shift($res);

    $this->db->disconnect();

    return intval($res['counter']);
  } // getQueiresPerSecond

  public function getQueriesByDay ( $date ) {
    $this->db->connect();

    // retrieve total served queries today
    $res = $this->db->exec("select count(*) as total from log_usage where date_format(`timestamp`, '%Y-%m-%d') = '{$date}' ");
    $res = array_shift($res);
    
    $this->db->disconnect();

    return $res['total'];
  } // getQueriesByDay

} // class
