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
   * @api {get} /<resources> Get "resources" 
   * 
   * @apiName getResources
   * @apiGroup Resources
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Retrieve a list of random strings. Default result set has 25 records. <br >
   *    It's possible to return a custom number of records passing <code>?results=X</code>, where X is the required number. <br ><br >
   *
   *    Specially for this method, since it is a extremely flexible method, you need to 
   *    tell the API what kind of data structure you are waiting for. <br ><br >
   *
   * @apiParam {integer} structure The structure data: <code>type:label:length:limit[,type:label:length:limit[,...]]</code>
   *
   * @apiParam (structure) {string="name","first_name","last_name","date","datetime","email","url","addr","int","dec","percent","string"} type The data type should be returned
   * @apiParam (structure) {string} label The label that should be applied to returned data field
   * @apiParam (structure) {integer} [length] The length of returned value. It's only used for <code>string</code>,<code>int</code>,<code>dec</code> and <code>percent</code>. Result will be random up to the length given. When <code>string</code> it defines how many words the sentence will have
   * @apiParam (structure) {integer} [min_length] The minimum length of returned value. It's only used for <code>string</code>,<code>int</code>,<code>dec</code> and <code>percent</code>. When <code>string</code> it defines how many words the sentence will have
   *
   * @apiExample {curl} Example:
   *  curl -X GET http://fakeapi.abtz.co/something?structure=name:name,date:birthday,string:something-else:3 or
   *  curl -X GET http://fakeapi.abtz.co/something?structure=name:name,date:birthday,string:something-else:3&results=50
   *
   * @apiSuccessExample JSON Success Response
   *  {"status":200,"data":[{"id":"1","first_name":"Allan"}]}
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

  /**
   * @api {post} /<resources> Create "resources" 
   * 
   * @apiName postResources
   * @apiGroup Resources
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Simulate a new record insert. When calling this method, whatever is passed as <code>POST data</code> 
   *    will be returned merged to the <code>resource</code> object. 
   *
   * @apiExample {curl} Example:
   *  curl -X POST \
   *    -H "Content-Type: application/x-www-form-urlencoded" \
   *    -d 'id=123&first_name=Jim&last_name=Cook' \
   *    "https://fakeapi.abtz.co/something"
   *
   * @apiParam (POST) {integer} id A fake ID for given user
   * @apiParam (POST) {variant} [anything-else] You can pass anything you want.
   *
   * @apiSuccessExample JSON Success Response
   *  {"status": 201,"data": {"first_name": "Jim", "last_name": "Cook","id": "123"}}
   *
   * @apiErrorExample JSON Error Response
   *  {"error":400,"message":"Bad Request","responseJSON":{"error":"missing_key","error_message":"ID is missing."}}
   */
  public function index_post () {

    global $cfg, $router;

    $post = $router::post();

    if (!$post || !isset($post['id'])) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $post['registration_date'] = date('Y-m-d H:i:s');

    return $router::response(201, $post);

  } // index_post

  /**
   * @api {put} /<resources>/:id Update "resources" 
   * 
   * @apiName putResources
   * @apiGroup Resources
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Simulate a record update. When calling this method, whatever is passed as <code>PUT data</code> 
   *    will be returned merged to the <code>resource</code> object. 
   *
   * @apiExample {curl} Example:
   *  curl -X PUT \
   *    -H "Content-Type: application/x-www-form-urlencoded" \
   *    -d 'id=123&first_name=Jim&last_name=Cook' \
   *    "https://fakeapi.abtz.co/something/234"
   *
   * @apiParam {integer} id A fake ID for given user
   * @apiParam (PUT) {variant} [anything-else] You can pass anything you want.
   *
   * @apiSuccessExample JSON Success Response
   *  {"status":200,"data":{"first_name":"Jim","last_name":"Cook","id":"234","update_date":"2016-11-25 11:36:20"}}
   *
   * @apiErrorExample JSON Error Response
   *  {"error":400,"message":"Bad Request","responseJSON":{"error":"missing_key","error_message":"ID is missing."}}
   */
  public function index_put ( $id = false ) {

    global $cfg, $router;

    if (!$id) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    $data = $router::put();
    $data['update_date'] = date('Y-m-d H:i:s');

    return $router::response(200, $data);

  } // index_put

  /**
   * @api {delete} /<resources>/:id Delete "resource" 
   * 
   * @apiName deleteResource
   * @apiGroup Resources
   * @apiVersion 1.0.0
   *
   * @apiDescription 
   *    Simulate a record exclusion.  
   *
   * @apiExample {curl} Example:
   *  curl -X DELETE "https://fakeapi.abtz.co/something/234"
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

    global $cfg, $router;

    if (!$id) {
      return $router::response(400, ['error' => 'missing_key', 'error_message' => 'ID is missing.']);
    }

    return $router::response(200, ['message' => 'Deleted']);

  } // index_delete

} // class