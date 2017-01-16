# Seed-PHP Microframework

> The stupidly easy to learn and use PHP microframework!

A microframework is a term used to refer to minimalistic web 
application frameworks. It lacks most of the functionality which 
is common to expect in a full-fledged web application framework.

Seed-PHP is a microframework that offers you a really simple way 
to implement simple, small but powerfull ```RESTfull APIs``` that could 
support pretty much all needed methods and response in ```JSON``` or ```XML``` 
formats. 


## Get started 

You can either fork/clone this repository or install this package using 
**composer**. As for composer, check the sample below out:

```sh
$ php composer create-project abtzco/seed-php 
```

After having create a project with the above command, go to the 
recently created **seed-php** folder and create a new `index.php` file, 
such as below:

```php
<?php 

// include composer autoloader
include './vendor/autoload.php';

// initilise the Seed-PHP App class
$app = \Seed\App::getInstance();

// Define a initial route 
$app->route('GET /', function () {
  echo 'Hello Word!';
});

// Let's rock ...
$app->run();

```

If you have forked or cloned the repository from Github, then instead of 
include the 'autoload.php' from composer, include the package loader:

```php
// include composer autoloader
//include './vendor/autoload.php';

// include package loader
include './vendor/seed-php/loader.php';

```

Now, as a final step, we must create the `.htaccess` file (in the same folder of 
the index.php) which will contain the necessary settings for Apache properly use 
the `mod_rewrite` and route all the traffic thru our `index.php`. Create the file 
as follow:

```sh
 # --------------------------------------------------------
 # Seed-PHP Microframework.
 # @see http://github.com/rogeriotaques/seed-php
 # -------------------------------------------------------- 

<IfModule mod_rewrite.c>
    RewriteEngine On
    #RewriteBase /

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

```

Done! Now, do access your project on any browser. ;)


## Get involved

This is an open-source project, which means that you can also contribute. 
To make your contributions, fork this repository, do your changes or 
improvements and create a pull request.


## Get support

Found an issue or would like to suggest something? <br>
Just go to [this page](https://github.com/AbtzCo/seed-php/issues) and 
open a ticket.

## Documentation

### Methods 

#### ```\Seed\App::getInstance()```

The core engine app is a singleton. All you need to do 
is get its instance to be able to use it.

#### ```\Seed\App()->run()```

After define all routes and settings you need for your 
tyni app, just call this method (pretty much at the bottom 
of your index file).

#### ```\Seed\App()->header( string:optional )```

Retrieve all headers from request. If a key is provided, 
then only the header that matches the given key will be 
returned, or false, when not found.

#### ```\Seed\App()->post( string:optional )```

Retrieve all posted data from a POST request. 

If a key is provided, then only the record that 
matches the given key will be returned, or false, 
when not found.

#### ```\Seed\App()->get( string:optional )```

Retrieve all data passed as query-string from a request. 

If a key is provided, then only the record that 
matches the given key will be returned, or false, 
when not found.

#### ```\Seed\App()->file( string:optional )```

Retrieve all information about uploaded files. 

If a key is provided, then only the record that 
matches the given key will be returned, or false, 
when not found.

#### ```\Seed\App()->cookie( string:optional )```

Retrieve all cookies from a request. 

If a key is provided, then only the record that 
matches the given key will be returned, or false, 
when not found.

#### ```\Seed\App()->put( string:optional )```

Retrieve all data posted to a PUT request. 

If a key is provided, then only the record that 
matches the given key will be returned, or false, 
when not found.

#### ```\Seed\App()->request()```

Returns an object with relevante information about 
the current request. E.g:

```json
{
  "base" : "http://localhost/seed-php",
  "method" : "GET",
  "endpoint" : "sample",
  "verb" : "anything",
  "id" : NULL,
  "args" : []
}
```

#### ```\Seed\App()->load( string:required, array:optional, string:optional)```

Loads a helper and makes it available from your instance 
of ```\Seed\App()```. First parameter is the helper name,
second is the helper config (if any), and third is the alias 
name that you would like to attach the helper in the instance.

```php
$app->load('mysql', [ 'base' => 'test' ], 'db');
```

#### ```\Seed\App()->route( string:required, function:optional )```

Define a route to your app. E.g:

```php
$app->route('GET /', function () {});
```

You can define multiples methods, such as:

```php
$app->route('GET|POST /', function () {});
```

In case you don't provide a method, ```GET``` will be assumed.

```php
// for this route, GET is assumed
$app->route('/', function () {});
```

#### ```\Seed\App()->response( code:optional, data:optional )```

If code is not given, then ```200``` is assumed.


#### ```\Seed\App()->setAllowedMethod( string:required, boolean:optional )```

#### ```\Seed\App()->setAllowedHeader( string:required, boolean:optional )```

#### ```\Seed\App()->setAllowedOrigin( string:required  )```

#### ```\Seed\App()->setCache( boolean:required, int:optional  )```

#### ```\Seed\App()->setLanguage( string:required  )```

#### ```\Seed\App()->setCharset( string:required  )```

#### ```\Seed\App()->setOutputType( string:required  )```

---

#### Available Helpers

| Helper Name | Remark |
| ------------| ------ |
| curl        | A full featured ccurl class. |
| logger      | A simple log class. Log data in a mysql database. |
| mysql       | A mysql wrapper. |
| http        | A http class helper for work with http response codes. |

> I know, details about helpers are missing ...
