<?php 

 /* --------------------------------------------------------
  | Users Controller
  | @author Rogerio Taques (rogerio.taques@gmail.com)
  | @version 0.1, 2016-11-22
  * -------------------------------------------------------- */

namespace Controllers;

defined('ENV') or die('Direct script access is not allowed!');

use Controllers\Master;

class Users extends Master {

  /**
   * @api {get} /api/rakuchat/rooms Get rooms 
   * 
   * @apiName getRooms
   * @apiGroup Rakuchat
   * @apiVersion 0.1.0
   *
   * @apiDescription Retrieve a list of public rooms from Rakuchat.
   *
   * @apiExample {curl} Example:
   *  curl -X GET http://bats-url/api/rakuchat/rooms
   *
   * @apiSuccessExample JSON Success Response
   *  {"status":200,"data":[{"id":"oQ6...vzD","name":"public-room-name"}]}
   *
   * @apiErrorExample JSON Error Response
   *  {"status":400,"data":[{"message": "Bad request"}]}
   */
  public function index_get ( $id = false ) {

    global $cfg, $router;

    if ($this->_structure !== false) {
      return $this->generalResource( $id );
    }

    $list = [];

    if ($id !== false) {
      $this->_results = 1;

      if ( intval($id) === -1 ) {
        return $router::response(404);
      }
    }

    for($i = 0; $i < $this->_results; $i++) {
      $list[$i] = $this->getUser($i, $id);
    }

    return $router::response(200, $list);

  } // index_get

  public function index_post () {

    global $cfg, $router;

    $post = $router::post();

    if (!$post || !isset($post['id'])) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $user = $this->getUser(0, $post['id']);
    $user['registration_date'] = date('Y-m-d H:i:s');

    return $router::response(201, $user);

  } // index_post

  public function index_put ( $id = false ) {

    global $cfg, $router;

    $post = $router::post();

    if (!$id) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $user = $this->getUser(0, $id);
    $user = array_merge($user, $post);
    $user['update_date'] = date('Y-m-d H:i:s');

    return $router::response(200, $user);

  } // index_put

  public function index_delete ( $id = false ) {

    global $cfg, $router;

    if (!$id) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    return $router::response(200, ['message' => 'Deleted']);

  } // index_delete

  private function getUser ($i, $id = false) {
    $gender = ['m','f'];
    $data = $name = $this->getRandName();

    $data['gender'] = $gender[rand(0,1)]; 
    $data['birthday'] = $this->getRandDate(); 
    $data['email'] = $this->getRandEmail( implode('.', $name) ); 
    $data['username'] = strtolower(implode('.', $name)); 
    $data['password'] = 'a100eed4'; 
    $data['address'] = $this->getRandAddr(); 
    $data['id'] = ( !$id ? $i + 1 : $id ); 

    return $data;
  }

} // class