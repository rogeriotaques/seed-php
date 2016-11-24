<?php 

 /* --------------------------------------------------------
  | Resources Controller
  | @author Rogerio Taques (rogerio.taques@gmail.com)
  | @version 0.1, 2016-11-22
  * -------------------------------------------------------- */

namespace Controllers;

defined('ENV') or die('Direct script access is not allowed!');

use Controllers\Master;

class Resources extends Master {

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

    if ($this->_structure === false) {
        $router::response(400, ['error' => 'bad_data_structure', 'error_message' => 'To use this method you should provide a data structure. Should be type:label:count[,type:label:count[,...]]']);
        exit;
    }
    
    return $this->generalResource( $id );

  } // index_get

  public function index_post () {

    global $cfg, $router;

    if (!$post || !isset($post['id'])) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $post['registration_date'] = date('Y-m-d H:i:s');

    return $router::response(201, $post);

  } // index_post

  public function index_put ( $id = false ) {

    global $cfg, $router;

    if (!$id) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $post['update_date'] = date('Y-m-d H:i:s');

    return $router::response(200, $post);

  } // index_put

  public function index_delete ( $id = false ) {

    global $cfg, $router;

    if (!$id) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    return $router::response(200, ['message' => 'Deleted']);

  } // index_delete

} // class