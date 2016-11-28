<?php 

 /* --------------------------------------------------------
 | PHP API KIT
 | @author Rogerio Taques (rogerio.taques@gmail.com)
 | @version 0.1
 | @license MIT
 | @see http://github.com/rogeriotaques/api-kit
 * -------------------------------------------------------- */

namespace Controllers;

defined('ENV') or die('Direct script access is not allowed!');

use Seed\Controller;
use Models\Status;

class Master extends Controller {

  const MAX_RESULTS = 250; 

  protected $_names;
  protected $_surnames;
  protected $_words;
  protected $_results;
  protected $_structure = false;
  protected $_tlds = ['.com', '.co', '.org', '.in', '.com.br', '.net', '.me'];
  protected $_images = [
    'M' => ['1074', '1062', '1005', '883', '804', '453'], 
    'F' => ['1027', '1011', '1010', '996', '978', '836']
  ]; 

  function __construct () {
    parent::__construct();

    // load our assets 
    $this->_results = $this->config['database'][ENV]['max-records-per-page'];
    $this->_names = $this->load('names');
    $this->_surnames = $this->load('surnames');
    $this->_words = $this->load('words');

    // is result given?
    if ( isset($_GET['results']) && is_numeric($_GET['results']) ) {
      if (intval($_GET['results']) > self::MAX_RESULTS) {
        $this->request->response(400, ['error' => 'max_results', 'error_message' => 'Max results is ' . self::MAX_RESULTS . ' records.']);
        exit;
      }

      $this->_results = $_GET['results'];
    }

    // is structure given?
    if ( isset($_GET['structure']) && is_string($_GET['structure']) ) {
      $structure = $_GET['structure'];

      if (strpos($structure, ':') === false) {
        $this->request->response(400, ['error' => 'bad_data_structure', 'error_message' => 'Data structure is baddly formed. Should be type:label:count[,type:label:count[,...]]']);
        exit;
      }

      $this->_structure = $this->breakStructureDown( $structure );
    }
  }

  private function load ( $file ) {
    $data  = [];
    $lines = 0;

    $file = "assets/data/{$file}.txt";

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

  protected function getRandMedia ($type = 'profile', $gender = 'M', $width = 600, $height = 600) {
    $gender  = strtoupper($gender);
    $source = 'https://unsplash.it/{width}/{height}?image={id}';

    if (!isset($this->_images[$gender])) {
      $gender = 'M';
    }

    if (!$width) {
      $width = 600;
    }

    if (!$height) {
      $height = $width;
    }

    switch ($type) {
      case 'image':
        $image  = rand(0, 1084);
        return [
          'image' => str_replace(['{width}', '{height}', '{id}'], [$width, $height, $image], $source),
          'thumb' => str_replace(['{width}', '{height}', '{id}'], ['100', '100', $image], $source)
        ];
        break;
      default: // avatar 
        $image  = $this->_images[$gender][rand(0, count($this->_images[$gender]) - 1)];
        return [
          'image' => str_replace(['{width}', '{height}', '{id}'], [$width, $height, $image], $source),
          'thumb' => str_replace(['{width}', '{height}', '{id}'], ['100', '100', $image], $source)
        ];
    }
  } // getRandMedia

  protected function getRandName ( $concat = false ) {
    $name = $this->_names->data[rand(0, $this->_names->count - 1)];
    list($name, $gender) = explode(',', $name);

    $data = [
      'first_name' => $name,
      'last_name' => $this->_surnames->data[rand(0, $this->_surnames->count - 1)]
    ];

    if ($concat === false) {
      $data['gender'] = $gender;
    }

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

  private function breakStructureDown ( $sttr ) {
    // echo print_r($sttr, true), "\n";

    // let's break it by arrays
    $sttr = explode('array:', $sttr);

    // remap the item separators 
    $sttr = array_map(function ($el) {
      if (strpos($el, ':[') !== false) {
        $el = str_replace([',', ':[', ']'], [';', '--', ''], $el);
        $el = str_replace([':'], ['..'], $el);
        return $el;
      }

      return $el; 
    }, $sttr);
    
    // echo print_r($sttr, true), "\n";

    // rebuild with new separator for array type
    $sttr =  implode('array|', $sttr);
    $sttr = preg_replace('/(,array\|)(\w+)(\-\-)/', ',array:$2:', $sttr);

    // echo print_r($sttr, true), "\n";

    // has user only passed arrays as structure?
    if (strpos($sttr, ',') === false) {
      // fallback the first level array to normal markup
      $sttr = preg_replace('/^(array\|)(\w+)(--)/', 'array:$2:', $sttr);
    }
    
    return array_map(function ($el) {
      list($type, $label, $count, $min_length, $extra) = explode(':', $el);

      return (object) [
        'type' => $type,
        'label' => $label ? $label : $type,
        'count' => $count ? $count : 1,
        'min_length' => $min_length ? $min_length : 1,
        'extra' => $extra ? $extra : null
      ];
    }, explode(',', $sttr));
  } // breakStructureDown
  
  private function createResource ( $item, $list ) {
    switch ($item->type) {
      case 'array':
        $array = $item->count;
        $array = str_replace(['..',';','|','--'], [':',',',':',':['], $array);
        $array = $this->breakStructureDown( $array );
        $array = array_map(function ($el) {
          return $this->createResource( $el, [] );
        }, $array);

        $list[$item->label] = $array;
        break;

      case 'name':
        $list[$item->label] = $this->getRandName(' ');
        break;

      case 'first_name':
        $name = $this->getRandName();
        $list[$item->label] = $name['first_name'];
        break;

      case 'last_name':
        $name = $this->getRandName();
        $list[$item->label] = $name['last_name'];
        break;

      case 'date':
        $list[$item->label] = $this->getRandDate();
        break;

      case 'datetime':
        $list[$item->label] = $this->getRandDate( true );
        break;

      case 'email': 
        $list[$item->label] = $this->getRandEmail(); 
        break;

      case 'url': 
        $list[$item->label] = $this->getRandURL(); 
        break;

      case 'addr': 
        $list[$item->label] = $this->getRandAddr(); 
        break;

      case 'int': 
        $list[$item->label] = $this->getRandNum($item->count, $item->min_length); 
        break;

      case 'dec': 
        $list[$item->label] = $this->getRandNum($item->count, $item->min_length, true); 
        break;

      case 'percent': 
        $list[$item->label] = $this->getRandNum($item->count, ($item->min_length > 2 ? $item->min_length : 2), true, true); 
        break;

      case 'avatar': 
      case 'image':
        $g = ( !is_numeric($item->count)
          ? $item->count
          : (!is_numeric($item->min_length)
            ? $item->min_length
            : ($item->extra
              ? $item->extra
              : 'M'
            )
          )
        );

        $w = (is_numeric($item->count) && $item->count > 1 ? $item->count : null);
        $h = (is_numeric($item->min_length) && $item->min_length > 1 ? $item->min_length : null);

        // var_dump($item, $g, $w, $h);

        $list[$item->label] = $this->getRandMedia($item->type, $g, $w, $h); 
        break;

      default: // string
        $list[$item->label] = $this->getRandString( $item->count ? $item->count : 1 ); 
    }

    return $list;
  } // createResource

  protected function getResource ( $id = false, $list = [] ) {
    if ($id !== false) {
      $this->_results = 1;

      if ( intval($id) === -1 ) {
        return $this->request->response(404);
      }
    }

    for($i = 0; $i < $this->_results; $i++) {
      $list[$i] = [];

      foreach( $this->_structure as $item ) {
        if (is_array($item)) {
          echo "It's an Array. ", print_r(array_shift($item), true), "\n";
          // foreach ($item as $subkey => $subitem) {
          //   echo "-- ", print_r($subitem), "\n";
          // }

          // $sublist = [];
          // $sublist_label = '';

          // foreach ($item as $subitem) {
          //   if (!is_null($subitem->array_label)) {
          //     $sublist_label = $subitem->array_label;
          //   }
          //   // echo "{$sublist_label}[{$subitem->type}:{$subitem->label}]<br >\n";
          //   $sublist = $this->createResource( $subitem, $sublist );
          // }

          // $list[$i][$sublist_label] = (array) $sublist;

          continue;
        }

        $list[$i] = $this->createResource( $item, $list[$i] );
      } 
    }

    return $list;
  }

  public function status_get () {
    $status = new Status();

    return $this->request->response(200, [
      'api_version' => $this->getVersion(),
      'names' => $this->_names->count,
      'surnames' => $this->_surnames->count,
      'words' => $this->_words->count,
      'images' => ( count($this->_images['M']) + count($this->_images['F']) ),
      'random_names' => ($this->_names->count * $this->_surnames->count),
      'queries' => $status->getTotalQueries(),
      'queries_per_day' => $status->getQueriesPerDay(),
      'queries_per_second' => $status->getQueriesPerSecond(),
      'queries_today' => $status->getQueriesByDay( date('Y-m-d') )
    ]);
  } // figures_get

} // class
