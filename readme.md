# PHP API KIT

Is a library kit, aka framework, that helps you to implement a RESTful API using PHP.

It's a simple and very lightweight framework, with a really small footprint, 
specially designed for APIs development and absolutelly not for MVC applications. 
Of course its source code architecure has some similarities to MVC frameworks, but
instead of using views for output, its outputs are string JSON encoded.

In the case you wanna something for MVC applications 
give a try on [Codeigniter](http://codeigniter.com) or [Zend Framework](https://framework.zend.com/).

## Get started

Clone this repository or download this project. Once you have the source files, you can 
start working on it. It's documentation and help pages are not ready (yet), but 
simply by looking at the source code you will be able to understand it.

## Structure

```sh
/ (root)
| assets/      # for eventual assets 
| config/      # where all config files are placed
| controllers/ # where your api controllers should be
| models/      # where data models should be placed 
| seed/        # here are the FW core 
| ----/ libraries/ 
| .htaccess    # it uses mod_rewrite from Apache
| index.php    # where everything starts ...
``` 

## Support

The framework, as well as this readme file, is on constant evolution.

If you find something wrong, or need some support, feel free to open an issue 
on github ( [here](https://github.com/rogeriotaques/php-api-kit/issues) ). 

Feel free to contribute or request! Send me your pull requests.

I'm gonna reply as soon as possible.
