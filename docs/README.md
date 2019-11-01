# Seed-PHP Microframework

> The stupidly easy to learn and use PHP microframework!

A `microframework` is a minimalistic web application frameworks. It lacks most of the functionality which 
is common to expect in a full-fledged web application framework, but it offers a quick way of implementing 
applications, especially when it comes to developing the RESTful API layer of the applications.

**Seed-PHP** is a microframework that offers you a really simple (and intuitive) way to implement powerfull 
`RESTfull APIs` (or even server-side redered apps/ websites) supporting pretty much all the needed methods 
and responses in `JSON` or `XML` formats.

!> **BREAKING CHANGES FROM 1.0.x:** <br >
The _namespace_ was changed, so if you're updating from versions earlier than 1.0.0, you'll be required to 
(also) update your code, replacing the namespace from `Seed\` to `SeedPHP\`. 

Example:

```php
# Deprecated
use Seed\App;
use Seed\Helper\Http;

# Correct
use SeedPHP\App;
use SeedPHP\Helper\Http;

$app = new App( ... );
```

## Get started

Using `composer` it's really simple to get started!

#### with an ongoing project (with composer), use:

```sh
$ php composer require abtzco/seed-php
```

#### or, with a fresh project, use:

```sh
$ php composer create-project abtzco/seed-php
```

To make it work, your app will need a `index.php` file which will handle pretty much all 
the requests, running Seed-PHP. The filemay looks like the following example. If your 
project does not have it (yet), simply create a new `index.php` file as follows:

Example: `index.php`

```php
<?php

// include composer autoloader
include __DIR__ . '/vendor/autoload.php';

// initilise the Seed-PHP App class
$app = \SeedPHP\App::getInstance();

// Define a initial route
$app->route('GET /', function () {
  echo 'Hello Word!';
});

// Let's rock ...
$app->run();
```

> **when cloning/ forking the repository from Github** <br >
then instead of include the 'autoload.php' from composer, include the package loader, as the example below:

```php
// Replace this
// include __DIR__ . '/vendor/autoload.php';

// with this
include __DIR__ . '/seed-php/loader.php';
```

As a final step, create the `.htaccess` file (in the same folder of the `index.php`). This file will contain 
the necessary settings for Apache properly use the `mod_rewrite` and route all the traffic thru our `index.php`.

Example:

```sh

# SeedPHP
# © 2018, Abtz Labs. By Rogerio Taques.
# @see http://github.com/abtzco/seed-php

<FilesMatch "^(\.|\_)">
    # Deny access to filenames starting 
    # with dot(.) or underline(_)
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch ".(yml|yaml|log|sh)$">
    # Deny access to filenames with 
    # especific extensions
    Order allow,deny
    Deny from all
</FilesMatch>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

```

Done! Now, do access your project on any browser. 😉

## Get involved

This is an open-source project, which means you can also freely contribute to improve it and make 
it better. To do your contributions, fork this repository, do your changes or improvements and 
create a [pull request](https://github.com/abtzlabs/seed-php/pulls).

## Get support

Found an issue or would like to suggest something? Just go to 
[this page](https://github.com/AbtzCo/seed-php/issues) and open a ticket. 

## Be a sponsor

Sponsoring this project will help and motivate me to keep improving it and making it even better 
for you (and the whole open-source community) who is using this package. 

  - [🙇‍♂️ Be a sponsor with Github ](https://github.com/sponsors/rogeriotaques)
  - [☕️ Buy me a coffee! ($5.00)](https://paypal.me/AbtzLabs/5USD)
  - [💰 Donate any amount - You decide 😉](https://paypal.me/AbtzLabs)

