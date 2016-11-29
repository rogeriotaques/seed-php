define({ "api": [  {    "type": "delete",    "url": "/<resources>/:id",    "title": "Delete \"resource\"",    "name": "deleteResource",    "group": "Resources",    "version": "1.0.0",    "description": "<p>Simulate a record exclusion.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X DELETE \"https://lyrebird.abtz.co/something/234\"",        "type": "curl"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "integer",            "optional": false,            "field": "id",            "description": "<p>A fake ID for given user</p>"          }        ]      }    },    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\":200,\"data\":{\"message\":\"Deleted\"}}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"error\":400,\"message\":\"Bad Request\",\"responseJSON\":{\"error\":\"missing_key\",\"error_message\":\"ID is missing.\"}}",          "type": "json"        }      ]    },    "filename": "controllers/resources.php",    "groupTitle": "Resources"  },  {    "type": "get",    "url": "/<resources>?structure=<data>",    "title": "Get \"resources\"",    "name": "getResources",    "group": "Resources",    "version": "1.0.0",    "description": "<p>Retrieve a list of random strings. Default result set has 25 records. <br > It's possible to return a custom number of records passing <code>?results=X</code>, where X is the required number. <br ><br ></p> <p>Specially for this method, since it is a extremely flexible method, you need to tell the API what kind of data structure you are waiting for. <br ><br ></p>",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "integer",            "optional": false,            "field": "structure",            "description": "<p>The structure data: <code>type:label[:length[:min_length[:extra]]][,type:label[:length[:min_length[:extra]]][,...]]</code></p>"          }        ],        "structure": [          {            "group": "structure",            "type": "string",            "allowedValues": [              "\"name\"",              "\"first_name\"",              "\"last_name\"",              "\"date\"",              "\"datetime\"",              "\"email\"",              "\"url\"",              "\"addr\"",              "\"int\"",              "\"dec\"",              "\"percent\"",              "\"string\"",              "\"avatar\"",              "\"image\"",              "\"array\""            ],            "optional": false,            "field": "type",            "description": "<p>The data type should be returned</p>"          },          {            "group": "structure",            "type": "string",            "optional": false,            "field": "label",            "description": "<p>The label that should be applied to returned data field</p>"          },          {            "group": "structure",            "type": "variant",            "optional": true,            "field": "length",            "description": "<p>The length of returned value. Can be an <code>integer</code> or <code>string</code>.<br><br> It's used for types: <code>string</code>,<code>int</code>,<code>dec</code>, <code>percent</code>, <code>avatar</code>, <code>image</code> and <code>array</code>. <br ><br > Result will be random up to the length given. <br><br> When type is <code>string</code> it defines how many words the sentence will have.<br ><br > When type is <code>avatar</code> or <code>image</code> this should be the image gender (<code>M</code> or <code>F</code>) or image width (e.g: 600).<br><br> When type is <code>array</code> then, this should be the next level structure in an array like syntaxe. E.g: <code>array:myarray:[name:user_name,date:birthday]</code> <br></p>"          },          {            "group": "structure",            "type": "variant",            "optional": true,            "field": "min_length",            "description": "<p>The minimum length of returned value. Can be an <code>integer</code> or <code>string</code>.<br><br> It's used for types: <code>int</code>,<code>dec</code>, <code>percent</code>, <code>avatar</code> and <code>image</code>. <br ><br > Whenever type is <code>avatar</code> or <code>image</code> this can be the image gender (<code>M</code> or <code>F</code>) or image height (e.g: 600).<br><br> It's ignored when type is <code>array</code>.</p>"          },          {            "group": "structure",            "type": "string",            "optional": true,            "field": "extra",            "description": "<p>When type is <code>avatar</code> and count and min_length are been passed, this can be given.<br> It represents the image gender (<code>M</code> or <code>F</code>). <br><br> It's ignored when type is <code>array</code>.</p>"          }        ]      }    },    "examples": [      {        "title": "Example:",        "content": "curl -X GET http://lyrebird.abtz.co/something?structure=name:name,date:birthday,string:something-else:3",        "type": "curl"      },      {        "title": "Example 1:",        "content": "curl -X GET http://lyrebird.abtz.co/something?structure=name:name,date:birthday,string:something-else:3&results=50",        "type": "curl"      },      {        "title": "Example 2:",        "content": "curl -X GET http://lyrebird.abtz.co/something\n?structure=name:name,date:birthday,array:other:[username:user,password:pwd,avatar:media]",        "type": "curl"      }    ],    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\":200,\"data\":[{\"id\":\"1\",\"first_name\":\"Allan\"}]}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"status\":400,\"data\":[{\"message\": \"Bad request\"}]}",          "type": "json"        }      ]    },    "filename": "controllers/resources.php",    "groupTitle": "Resources"  },  {    "type": "post",    "url": "/<resources>",    "title": "Create \"resources\"",    "name": "postResources",    "group": "Resources",    "version": "1.0.0",    "description": "<p>Simulate a new record insert. When calling this method, whatever is passed as <code>POST data</code> will be returned merged to the <code>resource</code> object.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X POST \\\n  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n  -d 'id=123&first_name=Jim&last_name=Cook' \\\n  \"https://lyrebird.abtz.co/something\"",        "type": "curl"      }    ],    "parameter": {      "fields": {        "POST": [          {            "group": "POST",            "type": "integer",            "optional": false,            "field": "id",            "description": "<p>A fake ID for given user</p>"          },          {            "group": "POST",            "type": "variant",            "optional": true,            "field": "anything-else",            "description": "<p>You can pass anything you want.</p>"          }        ]      }    },    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\": 201,\"data\": {\"first_name\": \"Jim\", \"last_name\": \"Cook\",\"id\": \"123\"}}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"error\":400,\"message\":\"Bad Request\",\"responseJSON\":{\"error\":\"missing_key\",\"error_message\":\"ID is missing.\"}}",          "type": "json"        }      ]    },    "filename": "controllers/resources.php",    "groupTitle": "Resources"  },  {    "type": "put",    "url": "/<resources>/:id",    "title": "Update \"resources\"",    "name": "putResources",    "group": "Resources",    "version": "1.0.0",    "description": "<p>Simulate a record update. When calling this method, whatever is passed as <code>PUT data</code> will be returned merged to the <code>resource</code> object.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X PUT \\\n  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n  -d 'id=123&first_name=Jim&last_name=Cook' \\\n  \"https://lyrebird.abtz.co/something/234\"",        "type": "curl"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "integer",            "optional": false,            "field": "id",            "description": "<p>A fake ID for given user</p>"          }        ],        "PUT": [          {            "group": "PUT",            "type": "variant",            "optional": true,            "field": "anything-else",            "description": "<p>You can pass anything you want.</p>"          }        ]      }    },    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\":200,\"data\":{\"first_name\":\"Jim\",\"last_name\":\"Cook\",\"id\":\"234\",\"update_date\":\"2016-11-25 11:36:20\"}}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"error\":400,\"message\":\"Bad Request\",\"responseJSON\":{\"error\":\"missing_key\",\"error_message\":\"ID is missing.\"}}",          "type": "json"        }      ]    },    "filename": "controllers/resources.php",    "groupTitle": "Resources"  },  {    "type": "delete",    "url": "/users/:id",    "title": "Delete users",    "name": "deleteUsers",    "group": "Users",    "version": "1.0.0",    "description": "<p>Simulate a record exclusion.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X DELETE \"https://lyrebird.abtz.co/users/234\"",        "type": "curl"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "integer",            "optional": false,            "field": "id",            "description": "<p>A fake ID for given user</p>"          }        ]      }    },    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\":200,\"data\":{\"message\":\"Deleted\"}}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"error\":400,\"message\":\"Bad Request\",\"responseJSON\":{\"error\":\"missing_key\",\"error_message\":\"ID is missing.\"}}",          "type": "json"        }      ]    },    "filename": "controllers/users.php",    "groupTitle": "Users"  },  {    "type": "get",    "url": "/users",    "title": "Get users",    "name": "getUsers",    "group": "Users",    "version": "1.0.0",    "description": "<p>Retrieve a list of random names. Default result set has 25 records. <br > It's possible to return a custom number of records passing <code>?results=X</code>, where X is the required number.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X GET http://lyrebird.abtz.co/users",        "type": "curl"      },      {        "title": "Example 1:",        "content": "curl -X GET http://lyrebird.abtz.co/users?results=50",        "type": "curl"      }    ],    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\":200,\"data\":[{\"id\":\"1\",\"first_name\":\"Allan\"}]}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"status\":400,\"data\":[{\"message\": \"Bad request\"}]}",          "type": "json"        }      ]    },    "filename": "controllers/users.php",    "groupTitle": "Users"  },  {    "type": "post",    "url": "/users",    "title": "Create users",    "name": "postUsers",    "group": "Users",    "version": "1.0.0",    "description": "<p>Simulate a new record insert. When calling this method, whatever is passed as <code>POST data</code> will be returned merged to the <code>user</code> object.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X POST \\\n  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n  -d 'id=123&first_name=Jim&last_name=Cook' \\\n  \"https://lyrebird.abtz.co/users\"",        "type": "curl"      }    ],    "parameter": {      "fields": {        "POST": [          {            "group": "POST",            "type": "integer",            "optional": false,            "field": "id",            "description": "<p>A fake ID for given user</p>"          },          {            "group": "POST",            "type": "variant",            "optional": true,            "field": "anything-else",            "description": "<p>You can pass anything you want.</p>"          }        ]      }    },    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\": 201,\"data\": {\"first_name\": \"Jim\", \"last_name\": \"Cook\",\"gender\": \"f\",\"birthday\": \"1978-02-15\"},\"id\": \"123\"}}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"error\":400,\"message\":\"Bad Request\",\"responseJSON\":{\"error\":\"missing_key\",\"error_message\":\"ID is missing.\"}}",          "type": "json"        }      ]    },    "filename": "controllers/users.php",    "groupTitle": "Users"  },  {    "type": "put",    "url": "/users/:id",    "title": "Update users",    "name": "putUsers",    "group": "Users",    "version": "1.0.0",    "description": "<p>Simulate a record update. When calling this method, whatever is passed as <code>PUT data</code> will be returned merged to the <code>user</code> object.</p>",    "examples": [      {        "title": "Example:",        "content": "curl -X PUT \\\n  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n  -d 'id=123&first_name=Jim&last_name=Cook' \\\n  \"https://lyrebird.abtz.co/users/234\"",        "type": "curl"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "integer",            "optional": false,            "field": "id",            "description": "<p>A fake ID for given user</p>"          }        ],        "PUT": [          {            "group": "PUT",            "type": "variant",            "optional": true,            "field": "anything-else",            "description": "<p>You can pass anything you want.</p>"          }        ]      }    },    "success": {      "examples": [        {          "title": "JSON Success Response",          "content": "{\"status\":200,\"data\":{\"first_name\":\"Jim\",\"last_name\":\"Cook\",\"gender\":\"f\",\"birthday\":\"1971-11-10\",\"email\":\"sabrina.cox@visitant.com\",\"username\":\"sabrina.cox\",\"password\":\"a100eed4\",\"address\":{\"province\":\"Sematic\",\"city\":\"Hyperfocal\",\"address1\":\"Brasiers\",\"address2\":\"Layaways, 300-0\",\"postalcode\":\"399-1400\"},\"id\":\"234\",\"update_date\":\"2016-11-25 11:36:20\"}}",          "type": "json"        }      ]    },    "error": {      "examples": [        {          "title": "JSON Error Response",          "content": "{\"error\":400,\"message\":\"Bad Request\",\"responseJSON\":{\"error\":\"missing_key\",\"error_message\":\"ID is missing.\"}}",          "type": "json"        }      ]    },    "filename": "controllers/users.php",    "groupTitle": "Users"  }] });
