<?php 

class SeedUnitTests extends UnitTestCase {

  function testGetInstance () {
    $app = \Seed\App::getInstance();
    $this->assertIsA( $app, 'Seed\Core' );
  } // testGetInstance

  function testLoadHelper () {
    $app = \Seed\App::getInstance();
    $app->load('mysql', [], 'db');
    $this->assertTrue( isset($app->db) );
  } // testLoadHelper

  function testSetRoute () {
    $app   = \Seed\App::getInstance();
    $route = $app->route('GET /', function () {});
    $this->assertEqual( $route, $app );
  } // testSetRoute

  function testResponse () {
    $app   = \Seed\App::getInstance();
    $res = $app->response(200, ['simpletest' => 'success'], false);
    $this->assertTrue( 
      is_array($res) &&
      isset($res['code']) && 
      isset($res['result']) && 
      isset($res['result']['simpletest']) &&
      $res['code'] == 200 && 
      $res['result']['simpletest'] == 'success'
    );
  } // testSetRoute

} // class 
