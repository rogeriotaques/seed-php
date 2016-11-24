<?php 

/**
 * Uses http://simpletest.org for unit tests. 
 * @use $ php test/unit-tests.php 
 */

 define('ENV', 'development');

@require_once(dirname(__FILE__) . '/../config/settings.php');
require_once(dirname(__FILE__) . '/../libraries/rcurl.php');
require_once(dirname(__FILE__) . '/../vendor/simpletest/simpletest/autorun.php');

use Libraries\RCurl;

// pointing to my localhost ...
define('API_ENDPOINT', "http://{$cfg['base-url'][ENV]}");

class UserTest extends UnitTestCase {

  /**
   * Test GET users 
   */
  function testGetUser () {
    $curl = new RCurl(API_ENDPOINT . 'users');
    $resp = $curl->get();
    $this->assertTrue( is_object($resp) && isset($resp->status) && $resp->status === 200 && count($resp->data) === 25 );
  }

  /**
   * Test GET users defining results count
   */
  function testGetUserResultsDefined () {
    $curl = new RCurl(API_ENDPOINT . 'users?results=2');
    $resp = $curl->get();
    $this->assertTrue( is_object($resp) && isset($resp->status) && $resp->status === 200 && count($resp->data) === 2 );
  }
  
  /**
   * Test GET users giving ID
   */
  function testGetUserIDGiven () {
    $curl = new RCurl(API_ENDPOINT . 'users/20');
    $resp = $curl->get();

    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 && 
      count($resp->data) === 1 &&
      $resp->data[0]->id == 20 
    );
  }
  
  /**
   * Test GET users not found
   */
  function testGetUserNotFound () {
    $curl = new RCurl(API_ENDPOINT . 'users/-1');
    $resp = $curl->get();
    $this->assertFalse($resp);
  }
  
  /**
   * Test GET users custom structure
   */
  function testGetUserCustomStructure () {
    $curl = new RCurl(API_ENDPOINT . 'users?structure=name:name,date:birthday');
    $resp = $curl->get();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 &&
      count($resp->data) === 25 &&
      isset($resp->data[0]->name) && 
      isset($resp->data[0]->birthday) 
    );
  }
  
  /**
   * Test GET users custom structure and wrong structure type
   */
  function testGetUserCustomStructureWrongType () {
    $curl = new RCurl(API_ENDPOINT . 'users?structure=undefined-type:name');
    $resp = $curl->get();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 &&
      count($resp->data) === 25 &&
      isset($resp->data[0]->name) 
    );
  }
  
  /**
   * Test GET users custom structure and wrong structure type
   */
  function testGetUserStringID () {
    $curl = new RCurl(API_ENDPOINT . 'users/something');
    $resp = $curl->get();
    $this->assertFalse($resp);
  }
  
  /**
   * Test POST users
   */
  function testPost () {
    $curl = new RCurl(API_ENDPOINT . 'users');
    $resp = $curl->data(['id' => 532])->post();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 201 && 
      $resp->data->id == 532 
    );
  }
  
  /**
   * Test POST users missing ID
   */
  function testPostMissingID () {
    $curl = new RCurl(API_ENDPOINT . 'users');
    $resp = $curl->data(['foo' => 'bar'])->post();
    $this->assertFalse($resp);
  }
  
  /**
   * Test PUT users
   */
  function testPut () {
    $curl = new RCurl(API_ENDPOINT . 'users/123');
    $resp = $curl->data(['foo' => 'bar'])->put();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 && 
      $resp->data->id == 123 
    );
  }
  
  /**
   * Test PUT users missing ID
   */
  function testPutMissingID () {
    $curl = new RCurl(API_ENDPOINT . 'users');
    $resp = $curl->data(['foo' => 'bar'])->put();
    $this->assertFalse($resp);
  }
  
  /**
   * Test DELETE users
   */
  function testDelete () {
    $curl = new RCurl(API_ENDPOINT . 'users/999');
    $resp = $curl->delete();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 
    );
  }
  
  /**
   * Test DELETE users missing ID
   */
  function testDeleteMissingID () {
    $curl = new RCurl(API_ENDPOINT . 'users');
    $resp = $curl->delete();
    $this->assertFalse( $resp );
  }

}

// ---------------------------------------------------------------------- 


class ResourcesTest extends UnitTestCase {

  /**
   * Test GET resources 
   */
  function testGetResource () {
    $curl = new RCurl(API_ENDPOINT . 'resources?structure=name:name,date:birthday');
    $resp = $curl->get();
    $this->assertTrue( is_object($resp) && isset($resp->status) && $resp->status === 200 && count($resp->data) === 25 );
  }

  /**
   * Test GET resources defining results count
   */
  function testGetResourceResultsDefined () {
    $curl = new RCurl(API_ENDPOINT . 'resources?results=2&structure=name:name,date:birthday');
    $resp = $curl->get();
    $this->assertTrue( is_object($resp) && isset($resp->status) && $resp->status === 200 && count($resp->data) === 2 );
  }
  
  /**
   * Test GET resources giving ID
   */
  function testGetResourcesIDGiven () {
    $curl = new RCurl(API_ENDPOINT . 'resources/20?structure=name:name,date:birthday');
    $resp = $curl->get();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 && 
      count($resp->data) === 1 &&
      isset($resp->data[0]->name) 
    );
  }
  
  /**
   * Test GET resources not found
   */
  function testGetResourcesNotFound () {
    $curl = new RCurl(API_ENDPOINT . 'resources/-1?structure=name:name,date:birthday');
    $resp = $curl->get();
    $this->assertFalse($resp);
  }
  
  /**
   * Test GET resources missing structure
   */
  function testGetResourcesMissingStructure () {
    $curl = new RCurl(API_ENDPOINT . 'resources');
    $resp = $curl->get();
    $this->assertFalse($resp);
  }
  
  /**
   * Test GET resources custom structure and wrong structure type
   */
  function testGetResourcesCustomStructureWrongType () {
    $curl = new RCurl(API_ENDPOINT . 'resources?structure=undefined-type:name');
    $resp = $curl->get();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 &&
      count($resp->data) === 25 &&
      isset($resp->data[0]->name) 
    );
  }
  
  /**
   * Test GET resources custom structure and wrong structure type
   */
  function testGetResourcesStringID () {
    $curl = new RCurl(API_ENDPOINT . 'resources/something?structure=name:name,date:birthday');
    $resp = $curl->get();
    $this->assertFalse($resp);
  }


  /**
   * Test POST resources
   */
  function testPost () {
    $curl = new RCurl(API_ENDPOINT . 'users');
    $resp = $curl->data(['id' => 532])->post();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 201 
    );
  }
  
  /**
   * Test POST resources missing ID
   */
  function testPostMissingID () {
    $curl = new RCurl(API_ENDPOINT . 'resources');
    $resp = $curl->data(['foo' => 'bar'])->post();
    $this->assertFalse($resp);
  }
  
  /**
   * Test PUT resources
   */
  function testPut () {
    $curl = new RCurl(API_ENDPOINT . 'resources/123');
    $resp = $curl->data(['foo' => 'bar'])->put();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 
    );
  }
  
  /**
   * Test PUT resources missing ID
   */
  function testPutMissingID () {
    $curl = new RCurl(API_ENDPOINT . 'resources');
    $resp = $curl->data(['foo' => 'bar'])->put();
    $this->assertFalse($resp);
  }
  
  /**
   * Test DELETE resources
   */
  function testDelete () {
    $curl = new RCurl(API_ENDPOINT . 'resources/999');
    $resp = $curl->delete();
    $this->assertTrue( 
      is_object($resp) && 
      isset($resp->status) && 
      $resp->status === 200 
    );
  }
  
  /**
   * Test DELETE resources missing ID
   */
  function testDeleteMissingID () {
    $curl = new RCurl(API_ENDPOINT . 'resources');
    $resp = $curl->delete();
    $this->assertFalse($resp);
  }

}
