![""](http://ryanuber.github.com/veneer/veneer.png "")

[![Build Status](https://travis-ci.org/ryanuber/veneer.png)](https://travis-ci.org/ryanuber/veneer)

### Create a file named "hello.php" with the following contents :
```php
<?php
namespace veneer\endpoint\hello;
class v1 extends \veneer\call
{
    public $get = array('/:name' => 'hello');
    public function hello($args)
    {
        return $this->response->set("Hello, {$args['name']}!", 200);
    }
}
?>
```

### And a file called "run.php" with these contents:
```php
<?php
require 'veneer/veneer.php';
require 'hello.php';
\veneer\app::listen();
?>
```

### Run this command :
```
$ php run.php
veneer\http\server : Validating PHP environment...
veneer\http\server : Found endpoint: v1/hello
veneer\http\server : Listening on 0.0.0.0:8080
```

### Then try out your new API :
```
$ curl http://localhost:8080/v1/hello/world
"Hello, world!"
```

FAQ
---

### How can I get the output in another format?
```
$ curl http://localhost:8080/v1/hello/world?format=json
$ curl -H 'format: json' http://localhost:8080/v1/hello/world
```
The default serialization type is json, but you can configure that by
calling `\veneer\app::set_default('output_handler', 'xyz')`, where `xyz`
is any other output handler. You can also change the parameter that
indicates the output handler by calling
`\veneer\app::set_default('output_handler_param', 'xyz')`, where `xyz`
is the parameter name. Other built-in handlers include `serialize`,
`plain`, and `html`. You can easily add to the available output handlers.
Take a look in `veneer/output/handler/*` for some examples.

### How can I use this under Apache, Nginx, Lighttpd, or web server x?
.htaccess file or web server config
```
RewriteEngine On
RewriteRule ^(.*)$ run.php [QSA]
```

run.php file
```php
<?php
require 'veneer/veneer.php';
require 'hello.php';
// Note this call to run(), not listen()
\veneer\app::run();
?>
```

### What about some more advanced features?
This readme is kept to a minimum. You can read about some more advanced
functionality by reading the [wiki](https://github.com/ryanuber/veneer/wiki/_pages)
