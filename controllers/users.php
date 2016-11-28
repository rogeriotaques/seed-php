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
   * @api {get} /users Get users 
   * 
   * @apiName getUsers
   * @apiGroup Users
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Retrieve a list of random names. Default result set has 25 records. <br >
   *    It's possible to return a custom number of records passing <code>?results=X</code>, where X is the required number.
   *
   * @apiExample {curl} Example:
   *  curl -X GET http://fakeapi.abtz.co/users
   *
   * @apiExample {curl} Example 1:
   *  curl -X GET http://fakeapi.abtz.co/users?results=50
   *
   * @apiSuccessExample JSON Success Response
   *  {"status":200,"data":[{"id":"1","first_name":"Allan"}]}
   *
   * @apiErrorExample JSON Error Response
   *  {"status":400,"data":[{"message": "Bad request"}]}
   */
  public function index_get ( $id = false ) {
    if ($this->_structure !== false) {
      $list = $this->getResource( $id );
      return $this->request->response(200, $list);
    }

    $list = [];

    if ($id !== false) {
      $this->_results = 1;

      if ( intval($id) === -1 ) {
        return $this->request->response(404);
      }
    }

    for($i = 0; $i < $this->_results; $i++) {
      $list[$i] = $this->getUser($i, $id);
    }

    return $this->request->response(200, $list);
  } // index_get

  /**
   * @api {post} /users Create users 
   * 
   * @apiName postUsers
   * @apiGroup Users
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Simulate a new record insert. When calling this method, whatever is passed as <code>POST data</code> 
   *    will be returned merged to the <code>user</code> object. 
   *
   * @apiExample {curl} Example:
   *  curl -X POST \
   *    -H "Content-Type: application/x-www-form-urlencoded" \
   *    -d 'id=123&first_name=Jim&last_name=Cook' \
   *    "https://fakeapi.abtz.co/users"
   *
   * @apiParam (POST) {integer} id A fake ID for given user
   * @apiParam (POST) {variant} [anything-else] You can pass anything you want.
   *
   * @apiSuccessExample JSON Success Response
   *  {"status": 201,"data": {"first_name": "Jim", "last_name": "Cook","gender": "f","birthday": "1978-02-15"},"id": "123"}}
   *
   * @apiErrorExample JSON Error Response
   *  {"error":400,"message":"Bad Request","responseJSON":{"error":"missing_key","error_message":"ID is missing."}}
   */
  public function index_post () {
    $post = $router->post();

    if (!$post || !isset($post['id'])) {
      return $this->request->response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $user = $this->getUser(0, $post['id']);
    $user = array_merge($user, $post);
    $user['registration_date'] = date('Y-m-d H:i:s');

    return $this->request->response(201, $user);
  } // index_post

  /**
   * @api {put} /users/:id Update users 
   * 
   * @apiName putUsers
   * @apiGroup Users
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Simulate a record update. When calling this method, whatever is passed as <code>PUT data</code> 
   *    will be returned merged to the <code>user</code> object. 
   *
   * @apiExample {curl} Example:
   *  curl -X PUT \
   *    -H "Content-Type: application/x-www-form-urlencoded" \
   *    -d 'id=123&first_name=Jim&last_name=Cook' \
   *    "https://fakeapi.abtz.co/users/234"
   *
   * @apiParam {integer} id A fake ID for given user
   * @apiParam (PUT) {variant} [anything-else] You can pass anything you want.
   *
   * @apiSuccessExample JSON Success Response
   *  {"status":200,"data":{"first_name":"Jim","last_name":"Cook","gender":"f","birthday":"1971-11-10","email":"sabrina.cox@visitant.com","username":"sabrina.cox","password":"a100eed4","address":{"province":"Sematic","city":"Hyperfocal","address1":"Brasiers","address2":"Layaways, 300-0","postalcode":"399-1400"},"id":"234","update_date":"2016-11-25 11:36:20"}}
   *
   * @apiErrorExample JSON Error Response
   *  {"error":400,"message":"Bad Request","responseJSON":{"error":"missing_key","error_message":"ID is missing."}}
   */
  public function index_put ( $id = false ) {
    $data = $this->request->put();

    if (!$id) {
      return $this->request->response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $user = $this->getUser(0, $id);
    $user = array_merge($user, $data);
    $user['update_date'] = date('Y-m-d H:i:s');

    return $this->request->response(200, $user);
  } // index_put

  /**
   * @api {delete} /users/:id Delete users 
   * 
   * @apiName deleteUsers
   * @apiGroup Users
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Simulate a record exclusion.  
   *
   * @apiExample {curl} Example:
   *  curl -X DELETE "https://fakeapi.abtz.co/users/234"
   *
   * @apiParam {integer} id A fake ID for given user
   *
   * @apiSuccessExample JSON Success Response
   *  {"status":200,"data":{"message":"Deleted"}}
   *
   * @apiErrorExample JSON Error Response
   *  {"error":400,"message":"Bad Request","responseJSON":{"error":"missing_key","error_message":"ID is missing."}}
   */
  public function index_delete ( $id = false ) {
    if (!$id) {
      return $this->request->response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    return $this->request->response(200, ['message' => 'Deleted']);
  } // index_delete

  private function getUser ($i, $id = false) {
    $data = $name = $this->getRandName();

    unset($name['gender']);

    $data['birthday'] = $this->getRandDate(); 
    $data['email'] = $this->getRandEmail( implode('.', $name) ); 
    $data['username'] = strtolower(implode('.', $name)); 
    $data['password'] = 'a100eed4'; 
    $data['address'] = $this->getRandAddr(); 
    $data['media'] = $this->getRandMedia('avatar', $data['gender']);  

    $data['id'] = ( !$id ? $i + 1 : $id ); 

    return $data;
  }

} // class
