<?php 

 /* --------------------------------------------------------
  | Seed PHP - Master Controller
  |
  | @author Rogerio Taques (rogerio.taques@gmail.com)
  | @version 0.1
  | @license MIT
  | @see http://github.com/rogeriotaques/seed-php
  | 
  | Use this controller to implement methods that are common 
  | to the API and/or can be extended by other controllers.
  * -------------------------------------------------------- */

namespace Controllers;

defined('ENV') or die('Direct script access is not allowed!');

use Libraries\RMySQL;

class Master {

  const MAX_RESULTS = 500; 

  protected $_names;
  protected $_surnames;
  protected $_words;
  protected $_results = 25;
  protected $_tlds = ['.com', '.co', '.org', '.in', '.com.br', '.net', '.me'];
  protected $_structure = false;

  function __construct () {
    global $router;

    $this->_names = $this->load('names');
    $this->_surnames = $this->load('surnames');
    $this->_words = $this->load('words');

    if ( isset($_GET['results']) && is_numeric($_GET['results']) ) {
      if (intval($_GET['results']) > self::MAX_RESULTS) {
        $router::response(400, ['error' => 'max_results', 'error_message' => 'Max results is ' . self::MAX_RESULTS . ' records.']);
        exit;
      }

      $this->_results = $_GET['results'];
    }

    if ( isset($_GET['structure']) && is_string($_GET['structure']) ) {
      $structure = $_GET['structure'];

      if (strpos($structure, ':') === false) {
        $router::response(400, ['error' => 'bad_data_structure', 'error_message' => 'Data structure is baddly formed. Should be type:label:count[,type:label:count[,...]]']);
        exit;
      }

      $this->_structure = array_map(function ($el) {
        list($type, $label, $count, $min_length) = explode(':', $el);

        return (object) [
          'type' => $type,
          'label' => $label ? $label : $type,
          'count' => $count ? $count : 1,
          'min_length' => $min_length ? $min_length : 1
        ];
      }, explode(',', $structure));
    }
  }

  private function load ( $file ) {
    $data  = [];
    $lines = 0;

    $file = "data/{$file}.txt";

    // open file handler
    $handle = fopen($file, "r");

    // pull all words from file to memory 
    while( !feof($handle) ) {
      $data[] = trim(str_replace(["\t", "\r", "\n"], '', fgets($handle)));
      $lines++;
    }

    // close file handler 
    fclose($handle);

    return (object) [
      'data' => $data,
      'count' => $lines
    ];
  } // load

  protected function getRandName ( $concat = false ) {
    $data = [
      'first_name' => $this->_names->data[rand(0, $this->_names->count - 1)],
      'last_name' => $this->_surnames->data[rand(0, $this->_surnames->count - 1)]
    ];

    return $concat !== false ? implode($concat, $data) : $data;
  } // getName

  /**
   * @param {boolean} $datetime - When true returns a datetime value. 
   */
  protected function getRandDate ( $datetime = false ) {
    $start = strtotime('1970-01-01');
    $end   = strtotime(date('Y-m-d'));
    return date('Y-m-d' . ($datetime ? ' H:i:s' : ''), rand($start, $end));
  } // getRandDate

  protected function getRandString ( $length = 1, $concat = ' ' ) {
    $data = [];

    for( $i = 0; $i < $length; $i++) {
      $data[$i] = $this->_words->data[rand(0, $this->_words->count)];
    }

    return implode($concat, $data);
  } // getRandString
  
  /**
   * @param {boolean} $datetime - When true returns a datetime value. 
   */
  function getRandNum ( $length = 3, $min_length = 1, $is_float = false, $is_percent = false ) {
    $lim = intval( str_pad('1', $length - 1, '0', STR_PAD_RIGHT) );
    $min_length = ($min_length < 1 ? 1 : $min_length);

    $val = rand(0, $lim);

    if ($min_length > 1) {
      $val = str_pad($val.'', $min_length, '0', STR_PAD_RIGHT);
    }

    $val = $is_percent ? $val / 100 : $val;

    return sprintf( ($is_float ? '%.' . $min_length . 'f' : '%d'), $val );
  }

  protected function getRandEmail ( $account = false ) {
    if (!$account) {
      $account = "{$this->getRandString()}.{$this->getRandString()}"; 
    }    

    $domain = $this->getRandString();
    $tld = $this->_tlds[rand(0, count($this->_tlds) - 1)];
    return strtolower("{$account}@{$domain}{$tld}");
  } // getRandEmail

  protected function getRandURL () {
    $domain = $this->getRandString();
    $tld = $this->_tlds[rand(0, count($this->_tlds) - 1)];
    return strtolower("http://{$domain}{$tld}");
  } // getRandURL

  protected function getRandAddr () {
    return [
      'province' => ucfirst($this->getRandString()),
      'city' => ucfirst($this->getRandString()),
      'address1' => ucfirst($this->getRandString()),
      'address2' => ucfirst($this->getRandString()) . ', ' . $this->getRandNum( 4, 3 ) . '-' . $this->getRandNum( 1 ),
      'postalcode' => preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $this->getRandNum(7, 7))
    ];
  } // getRandEmail

  protected function generalResource ( $id = false ) {
    global $router;

    if ($id !== false) {
      $this->_results = 1;

      if ( intval($id) === -1 ) {
        return $router::response(404);
      }
    }

    $list = [];

    for($i = 0; $i < $this->_results; $i++) {
      $list[$i] = [];

      foreach( $this->_structure as $item ) {

        switch ($item->type) {
          case 'name':
            $list[$i][$item->label] = $this->getRandName(' ');
            break;

          case 'first_name':
            $name = $this->getRandName();
            $list[$i][$item->label] = $name['first_name'];
            break;

          case 'last_name':
            $name = $this->getRandName();
            $list[$i][$item->label] = $name['last_name'];
            break;

          case 'date':
            $list[$i][$item->label] = $this->getRandDate();
            break;

          case 'datetime':
            $list[$i][$item->label] = $this->getRandDate( true );
            break;

          case 'email': 
            $list[$i][$item->label] = $this->getRandEmail(); 
            break;

          case 'url': 
            $list[$i][$item->label] = $this->getRandURL(); 
            break;

          case 'addr': 
            $list[$i][$item->label] = $this->getRandAddr(); 
            break;

          case 'int': 
            $list[$i][$item->label] = $this->getRandNum($item->count, $item->min_length); 
            break;

          case 'dec': 
            $list[$i][$item->label] = $this->getRandNum($item->count, $item->min_length, true); 
            break;

          case 'percent': 
            $list[$i][$item->label] = $this->getRandNum($item->count, ($item->min_length > 2 ? $item->min_length : 2), true, true); 
            break;

          default: // string
            $list[$i][$item->label] = $this->getRandString( ($count ? $count : 1) ); 
        }
        
      } 
    }

    return $router::response(200, $list);
  }

  public function figures_get () {
    global $router, $cfg;

    $db = new RMySQL( $cfg['logger'][ENV] );
    $db->connect();

    // retrieve total served queries
    $res = $db->exec('select count(*) as total from log_usage');
    $res = array_shift($res);
    $total_queries = $res['total'];

    // retrieve total served queries per day
    $res = $db->exec('
      select avg(total) as counter from (
        select count(*) as total from log_usage group by unix_timestamp(`timestamp`) div 86400
      ) as A
    ');
    $res = array_shift($res);
    $queries_per_day = intval($res['counter']);

    // retrieve total served queries per second
    $res = $db->exec('
      select avg(total) as average from (
        select count(*) as total from log_usage group by unix_timestamp(`timestamp`) div 1
      ) as A
    ');
    $res = array_shift($res);
    $queries_per_sec = intval($res['counter']);

    // retrieve total served queries today
    $res = $db->exec('select count(*) as total from log_usage where date_format(`timestamp`, \'%Y-%m-%d\') = \'' . date('Y-m-d'). '\'');
    $res = array_shift($res);
    $queries_today = $res['total'];
    
    $db->disconnect();
    
    return $router::response(200, [
      'names' => $this->_names->count,
      'surnames' => $this->_surnames->count,
      'words' => $this->_words->count,
      'random_names' => ($this->_names->count * $this->_surnames->count),
      'queries' => $total_queries,
      'queries_per_day' => $queries_per_day,
      'queries_per_second' => $queries_per_sec,
      'queries_today' => $queries_today
    ]);
  } // figures_get

} // class