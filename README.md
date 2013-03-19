![""](http://ryanuber.github.com/veneer/veneer.png "")

[![Build Status](https://travis-ci.org/ryanuber/veneer.png)](https://travis-ci.org/ryanuber/veneer)

### Create a file named "hello.php" with the following contents :

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

### And a file called "run.php" with these contents:

    <?php
    require 'veneer/veneer.php';
    require 'hello.php';
    \veneer\app::listen();
    ?>

### Run this command :

    $ php run.php
    veneer\http\server : Validating PHP environment...
    veneer\http\server : Found endpoint: v1/hello
    veneer\http\server : Listening on 0.0.0.0:8080

### Then try out your new API :

    $ curl -s http://localhost:8080/v1/hello/world
    "Hello, world!"

That looks neat. So what is it?
-------------------------------

veneer is a small, basic API framework written in PHP that has no dependencies other
than PHP itself. It provides less than most other micro-frameworks do these days; it
doesn't focus on complex routing, appealing syntax, or making the 'hello world'
example as small as possible, but some basic features include

* Mandatory versioning
* Route matching, patterns, and splats
* Modular output handler layer
* Input validation

It also includes a stand-alone HTTP server implemented using sockets. It is not
required that you use it. In fact, in production, it is encouraged that you front-
end the veneer framework with a battle-hardened web server.

Err... why?
-----------

This framework does not aim to be perfect or satisfy every use case imagineable. So
why would anyone want to use it? The veneer framework focuses on:

* Versioning
* Documentation
* Validation
* Ease of development

Versioning
----------

Version definitions happen by naming your classes appropriately within your endpoint's
namespace. The 'hello world' example above shows the most common use case, a 'v1' API
endpoint. The framework does, however, allow for dot-release versioning. If you include
an underscore '_' character in the class name, this will be treated as a dot (.) in the
version during requests. You can add as many dot-releases as you'd like. So for example,
the next version (with backward compatibility, if you use semantic versioning), might
look like this:

    namespace veneer\endpoint\hello;
    class v1_1 extends \veneer\call {
        ...
    }

The above would be callable as `/v1.1/hello`.

Input validation
----------------

This framework provides two easy mechanisms for validating requests and input, and does
it in a way that unifies documentation and test. The following is a slightly more
complicated version of the hello world endpoint from above.

    <?php
    namespace veneer\endpoint\hello;
    class v1 extends \veneer\call
    {
        public $get = array(
            '/:name' => array(
                'function' => 'hello',
                'parameters' => array(
                    'name' => array(
                        'constraint' => '^[a-z]+$'
                    ),
                    'greeting' => array(
                        'required' => true,
                        'constraint' => '^[\w\s]+$',
                        'validate_fn' => 'validate_greeting'
                    )
                )
            )
        );

        public function hello($args)
        {
            return $this->response->set("Hello, {$args['name']}! {$args['greeting']}", 200);
        }

        public function validate_greeting($value)
        {
            return $value != 'How are you';
        }
    }
    ?>

The above example creates a required GET parameter with key 'greeting' and a value
that matches `^[\w\s]+$`, and returns true when passed through the `validate_greeting`
function. It also adds basic validation around the `name` parameter, which is actually
being set by the route definition. If you were to query this endpoint you would get
some results like the following:

Fails because `greeting` is missing

    $ curl -s localhost:8080/v1/hello/world
    Required parameter "greeting" missing

Fails because `validate_greeting` returns false

    $ curl -s localhost:8080/v1/hello/world?greeting=How%20are%20you
    Invalid value(s) for: greeting

Fails because `@` does not match `\w`

    $ curl -s localhost:8080/v1/hello/world?greeting=myself@mydomain.com
    Invalid value(s) for: greeting

Fails because the `:name` parameter (from the route) doesn't match `^[a-z]+$`

    $ curl -s http://localhost:8080/v1/hello/random%20person
    Invalid value(s) for: name

Succeeds

    $ curl -s localhost:8080/v1/hello/world?greeting=Pleased%20to%20meet%20you
    Hello, world! Pleased to meet you

This makes API endpoint code much more declarative, and keeps the functional code
cleaner by moving the testing piece to a higher layer. You of course are not obligated
to use the framework's built-in argument validator - You can still do validation
within your endpoint code. But it's good to know that you can use individual pieces of
it. For example, the 'required' field could be defined and nothing else - which would
simply make sure that the parameter existed, and then just pass the value blindly in
to your endpoint function.

Documenting your API endpoints
------------------------------

How you do this is really situational. But, I will say that keeping documentation near
and dear to the code it describes is always a good thing. That's why in the veneer
framework, it is relatively easy to expose documentation in any number of ways -
while keeping the documentation inside of the endpoint code itself. An advantage of
documenting your API endpoints in this way is that there is a large amount of synergy
between your documentation and functionality in the endpoint itself.

Example: In a typical scenario, you write some code that checks the value of the
'id' GET parameter in one of your endpoints. The code makes sure that this parameter
carries a numbers-only value. Then, you go into your documentation framework, and you
write documentation describing that exact same scenario.

This framework attempts to expose documentation on your routes by implementing a
default HTTP OPTIONS request method, without any additional code within the endpoint!
Let's look at the hello world example above. You could discover all of the available
routes within it, and any arbitrary documentation within it, by simply querying the
API endpoint like so:

    $ curl -X OPTIONS http://localhost:8080/v1/hello
    {
      "get":{
        "\/:name":{
          "function":"hello",
          "parameters":{
            "name":{
              "constraint":"^[a-z]+$"
            },
            "greeting":{
              "required":true,
              "constraint":"^[\\w\\s]+$",
              "validate_fn":"validate_greeting"
            }
          }
        }
      },
      "post":[],
      "put":[],
      "delete":[]
    }

This lets you fetch valuable data about the endpoint internals, in a way that precisely
reflects code being evaluated. What's great about that is you can still choose the
format you want it in by the normal means, like `OPTIONS /v1/hello.serialize`.

Since veneer uses arbitrary arrays to define endpoint routes, this means that you can
also add custom fields as you see fit to your routes, or anywhere else withing the
$get, $post, $put, or $delete class variables, and they would be exposed by the above
example if a user queried for documentation.

The main point is that how you expose the documentation is up to you. The framework
does not make it mandatory, and does not try to impose strict documenting standards.
However, it makes an attempt to make it easy for developers to convey easily exactly
what they intended their endpoints to do, and what their options are for how to make
calls to it.

Handling Output
---------------

The output handler layer in this framework is pluggable. By default, it uses the widely-
accepted JSON encoding type. This default is settable on a per-route basis. Using the
simplistic 'hello world' example from above, you could make a small modification to
alter the default output handler used by the `/:name` route by doing the following:

    public $get = array('/:name' => array(
        'output_handler' => 'serialize',
        'function' => 'hello'
    );

Notice that instead of the most basic 'route' => 'function_name' syntax, the value is
changed to an array so that we can specify both the function to call and the default
output handler to use for the endpoint. If the default output handler is invalid, and
no other output handlers were specified during the query, an error will be thrown.

You can also override the default output handler by setting it before calling run()
or listen():

    \veneer\app::set_default('output_handler', 'serialize');
    \veneer\app::run();

Also included out-of-the-box is the php 'serialize' type, and a plain text outputter.
Originally, XML was also included, but due to the varying ways in which PHP can be
compiled, and the fundamental differences between XML and other serializers,
the built-in implementation has been removed in favor of simplicity.

You can invoke any API endpoint with any output handler by specifying it in the query
parameters of the request, for instance:

    $ curl -s http://localhost:8080/v1/hello/world?format=serialize
    a:4:{s:8:"endpoint";s:5:"hello";s:7:"version";s:2:"v1";s:4:"status";i:200;s:8:"response";s:13:"Hello, world!";}
    $ curl -s http://localhost:8080/v1/hello/world?format=plain
    Hello, world!

Don't like using the query parameter "format"? No problem! You can change the default
parameter name which indicates the output mechanism, and you can also specify it on a per-
route basis. In best practice, you would never use a different query parameter in different
API endpoints to specify the output handler, but veneer does not prevent you from doing that.

The following will change the default parameter name to "output_type":

    \veneer\app::set_default('output_handler_param', 'output_type');
    \veneer\app::run();

You can specify the same inside of your route definitions.

You can also pass output types using the HTTP "Accept" header, like this:

    $ curl -s -H 'Accept: application/json' http://localhost:8080

With these two different methods of specifying an output handler, internally veneer will use
the following priority to determine how to send output back to you:

* The `Accept` header
* The output handler parameter for the route (default: `format`)
* The default output handler for the route (default: `json`)
* text/plain, an error message, and a `415 Unsupported Media Type` response.

There are constraints around output handlers, specifically what types of data can be handled.
There are 2 main types that any outputter might support: string and array. Any handler that can
handle array and string data would satisfy both of these. However, there might be some
output handlers that cannot (for whatever reason) handle one or the other. An example is the
plain outputter - it can handle dumping a raw string or integer, but it does not know how to
dump an array in raw format. These constraints are settable on a per-handler basis within the
handler class by implementing the appropriate interfaces, \veneer\output\str for string
handlers, and \veneer\output\arr for array handlers. As an example, the json outputter would
look something like the following:

    namespace veneer\output\handler;
    class json implements
        \veneer\output\arr,
        \veneer\output\str
    {
        ...

Route Splats
------------

Currently route splats are supported in a limited form. The most common use for route
splats seems to be emulating a "default", or "fallback" class method, just in case
the user's query does not match one of the routes you have defined for them. Typically,
when you make a request to an endpoint for a route it does not know about, you would
see something similar to the following:

    Incomplete response data returned by endpoint

While this might be good enough for some, others will want to write out their own
custom message, examine query parameters, etc. You can do this today in the veneer
framework, simply by using the '*' character in your route definitions. An example,
keeping in mind that routes are evaluated in the order they are defined, might look
like the following:

    public $get = array(
        '/:name' => 'hello',
        '*' => 'helpme'
    );

    public function hello($args)
    {
        return $this->response->set("Hello, {$args['name']}!", 200);
    }

    public function helpme($args)
    {
        return $this->response->set('You have to tell me who to say hello to...', 400);
    }

The above example code would only respond successfully if you have included a name
in your query as the first route element. If I make any other query, I will be
served a help message and a 400 status code to let me know what I did wrong.

You of course are not limited to only using route splats as a way of implementing
fallback methods. You can stick an asterisk (*) anywhere in your route and they
should otherwise perform exactly the same as a standard route.

Stand-Alone Socket Server
-------------------------

Included with veneer is a small and simple HTTP server implemented using sockets. This
is great for development purposes. However, it is not recommended for use in high-
concurrency or other production-type environments. In that case, it is much better to
use Apache, Nginx, lighttpd, or whatever your non-ghetto webserver of choice is.

The `\veneer\app::listen()` method will start an HTTP server instance on 0.0.0.0:8080
(the default). You can change the bind options in the call with:

    \veneer\app::listen('0.0.0.0', '8080');

Apache (and similar for other full-featured web servers)
--------------------------------------------------------

Example virtual host with mod_rewrite

    Listen 8080
    <VirtualHost *:8080>
        DocumentRoot /var/www/api-endpoints
        <Directory "/var/www/api-endpoints">
            RewriteEngine On
            RewriteRule ^(.*)$ index.php [QSA]
        </Directory>
    </VirtualHost>

index.php file at /var/www/api-endpoints/index.php

    <?php
    require 'veneer/veneer.php';
    require 'myapp.php';
    \veneer\app::run();
    ?>

Building a binary package
-------------------------

You can build a binary .rpm package for RHEL systems by doing the following:

    git clone git://github.com/ryanuber/veneer
    tar czf veneer.tar.gz veneer/
    rpmbuild -tb veneer.tar.gz

Coding Standards
----------------

The veneer framework aims to be compliant with the PSR-2 coding style guide.
