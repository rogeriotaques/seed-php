# Seed-PHP Microframework

> The stupidly easy to learn and use PHP microframework!

![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/AbtzLabs/seed-php?label=Version)

A `microframework` is a term used to refer to minimalistic web
application frameworks. It lacks most of the functionality which
is common to expect in a full-fledged web application framework.

Seed-PHP is a microframework that offers you a really simple way
to implement powerfull `RESTfull APIs` or even simple websites that could support pretty much all needed methods and responses in `JSON` or `XML` formats.

Check the complete [documentation](https://github.com/AbtzCo/seed-php/tree/master/docs).

> **NOTE OF BREAKING CHANGES:**
> The _namespace_ was modified, so if you're updating from previous versions (prior to 1.0.0), you'll be required to update your code, replacing namespace from `Seed\` to `SeedPHP\`. E.g:

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

Using `composer` it's really simple get started!

If you have a project on going with composer, use:

```sh
$ php composer require abtzco/seed-php
```

or, to start a project from scratch, use:

```sh
$ php composer create-project abtzco/seed-php
```

Your application should have a `index.php` which may looks like the
following example. Or simply create a new `index.php` file as follows:

Example: (`index.php`)

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

If you have forked or cloned the repository from Github, then instead of
include the 'autoload.php' from composer, include the package loader:

```php
// include composer autoloader
// include __DIR__ . '/vendor/autoload.php';

// include package loader
include __DIR__ . '/seed-php/loader.php';
```

Now, as a final step, we must create the `.htaccess` file (in the same folder of the `index.php`) which will contain the necessary settings for Apache properly use the `mod_rewrite` and route all the traffic thru our `index.php`.

Example:

```sh
 # --------------------------------------------------------
 # Seed-PHP Microframework.
 # @see http://github.com/abtzco/seed-php
 # --------------------------------------------------------

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

Done! Now, do access your project on any browser. ;)

## Get involved

This is an open-source project, which means you can also freely
contribute to improve it and make it better. To do your contributions, fork this repository, do your changes or improvements and create a [pull request](https://github.com/abtzlabs/seed-php/pulls).

## Get support

Found an issue or would like to suggest something? Just go to [this page](https://github.com/AbtzCo/seed-php/issues) and open a ticket. Learn more [about us](https://abtz.co).

## Be a sponsor

Sponsoring this project will help and motivate me to keep improving it and making it even better for you (and the whole open-source community) who is using this package. 

  - [☕️ Buy me a coffee! ($5.00)](https://paypal.me/AbtzLabs/5USD)
  - [💰 Donate any amount - You decide 😉](https://paypal.me/AbtzLabs)

