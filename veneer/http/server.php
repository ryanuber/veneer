<?php
/**
 * veneer - An Experimental API Framework for PHP
 *
 * @author     Ryan Uber <ru@ryanuber.com>
 * @copyright  Ryan Uber <ru@ryanuber.com>
 * @link       https://github.com/ryanuber/veneer
 * @license    http://opensource.org/licenses/MIT
 * @package    veneer
 * @category   api
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace veneer\http;

/**
 * server
 *
 * A micro-HTTP server to front-end the underlying API code when a web
 * server is either unavailable or impractical. This also makes
 * testing changes very easy for a developer (no need to run heavyweight
 * web servers on your desktop).
 *
 * Using this method of front-ending the API is very lightweight and very
 * fast. All that is required are the common PHP libraries and the CLI
 * tools to invoke them. In both Ubuntu and RHEL-based distributions, these
 * packages would be php-common and php-cli.
 *
 * Note that this is not dependent on php-cgi. This micro framework handles
 * raw TCP connections and parses HTTP messages all on its own.
 */
class server
{
    /**
     * @object  An instance of a socket connection:
     */
    private $socket;

    /**
     * @var string  Address to bind the socket server on
     */
    private $bind_addr;

    /**
     * @var integer  TCP port to bind the socket server on
     */
    private $bind_port;

    /**
     * @var integer  The maximum amount of time that socket_select will block for
     */
    private $timeout = 3;

    /**
     * @var integer  The maximum number of socket events to keep in the back log
     */
    private $backlog_max = 64;

    /**
     * @var integer  The maximum amount of buffer from each request to read.
     */
    private $max_buffer = 4096;

    /**
     * @var array  A list of functions that are required for the socket server to operate.
     */
    private $prerequisites = array(
        'socket_create',
        'socket_set_option',
        'socket_bind',
        'socket_listen',
        'socket_select',
        'socket_accept',
        'socket_close',
        'socket_read',
        'ob_start',
        'ob_get_clean'
    );

    /**
     * Constructor to initialize the socket server listener. The user passes in
     * the interface to bind to and the port number to bind on in one call, and
     * as a result they get a functioning HTTP server running the API code.
     *
     * @param string $bind_addr  The address to bind the server to
     * @param integer $bind_port  The port number to listen on
     * @return bool
     */
    public function __construct($bind_addr, $bind_port)
    {
        self::validate_php();
        self::discover_endpoints();
        self::set_bind_addr($bind_addr);
        self::set_bind_port($bind_port);

        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, 0)) === false) {
            throw new \veneer\exception\socket('Failed while creating new socket');
        }

        if ((socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, true)) === false) {
            throw new \veneer\exception\socket('Failed to set REUSEADDR on new socket');
        }

        if ((socket_bind($this->socket, $bind_addr, $bind_port)) === false) {
            throw new \veneer\exception\socket('Failed to bind new socket on '.$bind_addr.' port '.$bind_port);
        }

        self::listen();
        self::server();
    }

    /**
     * Write debugging messages
     *
     * @param string $message  The message to write
     * @return bool
     */
    private function debug($message)
    {
        printf("%s : %s\n", __CLASS__, $message);
    }

    /**
     * Set the bind address
     *
     * @param string $Address  The address to bind on
     * @return bool
     */
    public function set_bind_addr($Address='0.0.0.0')
    {
        return $this->bind_addr = $Address;
    }

    /**
     * Get the bind address
     *
     * @return integer
     */
    public function get_bind_addr()
    {
        return $this->bind_addr;
    }

    /**
     * Set the bind port
     *
     * @param string $Port  The TCP port to bind on
     * @return bool
     */
    public function set_bind_port($Port=8080)
    {
        return $this->bind_port = $Port;
    }

    /**
     * Get the bind port
     *
     * @return integer
     */
    public function get_bind_port()
    {
        return $this->bind_port;
    }

    /**
     * Set the maximum time for socket_select() to block
     *
     * @param integer $timeout  The maximum allowed blocking time
     * @return bool
     */
    public function set_timeout($timeout)
    {
        return $this->timeout = (int)$timeout;
    }

    /**
     * Get the timeout for socket_select()
     *
     * @return integer
     */
    public function get_timeout()
    {
        return $this->timeout();
    }

    /**
     * Ensure that the required PHP functions are available on this platform before
     * attempting to call them. This gives us more predictable results and provides
     * some insurance that if the server does start successfully, none of the required
     * functions are missing or disabled in php.ini.
     *
     * @return bool
     */
    public function validate_php()
    {
        self::debug('Validating PHP environment...');
        foreach ($this->prerequisites as $function) {
            if (!function_exists($function)) {
                throw new \veneer\exception\socket('Required function is not available: '.$function);
            }
        }
    }

    /**
     * Check that there are endpoints defined. If there are no endpoints defined,
     * the end user should be alerted as the server is starting up.
     *
     * @return bool
     */
    public function discover_endpoints()
    {
        foreach (\veneer\util::get_endpoints() as $version => $endpoints) {
            foreach ($endpoints as $endpoint) {
                self::debug('Found endpoint: '.\veneer\util::version('number', $version).'/'.$endpoint);
            }
        }
    }

    /**
     * This function tells the socket server to begin listening. You are able to
     * configure the maximum number of events to keep in the backlog to prevent
     * server overloading.
     *
     * @return bool
     */
    public function listen()
    {
        self::debug('Listening on '.$this->bind_addr.':'.$this->bind_port);
        if ((socket_listen($this->socket, $this->backlog_max)) === false) {
            throw new \veneer\exception\socket('Failed to listen on address '.$this->bind_addr.' port '.$this->bind_port);
        }
    }

    /**
     * Main server method. This will loop as long as there is a master server
     * socket present and handle the flow of incoming requests.
     *
     * @param integer $max_request_size  The maximum size (in bytes) of a request
     * @return bool
     */
    public function server($max_request_size=4096)
    {
        $r = array($this->socket);
        while ((socket_select($r, $w=NULL, $e=NULL, $this->timeout)) !== false) {
            $client = socket_accept($this->socket);

            /**
             * Since this is HTTP over sockets, you can't just assume that all of the
             * data will be sent in one packet and gathered with one recv(). Here we
             * parse the HTTP request according to RFC2616 (HTTP Message / Hypertext
             * Transfer Protocol -- HTTP/1.1) to read chunked messages and length
             * headers to read continuously until all parts of the HTTP message have
             * been received.
             */
            $complete = false;
            $buf = $input = '';
            while (!$complete) {
                $buf = socket_read($client, (int)$max_request_size);
                $input .= $buf;
                $request = \veneer\http\request::from_message($input);
                if (!$request) {
                    continue;
                }
                if (array_key_exists('content-length', $request['headers'])) {
                    $complete = (strlen($request['body']) == $request['headers']['content-length']);
                } else {
                    $complete = true;
                }
            }

            socket_getpeername($client, $request['remote_addr'], $request['remote_port']);
            self::set_environment($request);

            /**
             * Don't do anything for requests for favicon.ico
             */
            if ($_SERVER['REQUEST_URI'] == 'favicon.ico') {
                socket_close($client);
                $r = array($this->socket);
                continue;
            }

            ob_start();
            \veneer\app::run();
            socket_write($client, ob_get_clean());

            /**
             * Close the socket and add the master server socket back into the pool. The
             * socket_select() function modifies data passed to it so the master server
             * socket needs to be re-added.
             */
            socket_close($client);
            $r = array($this->socket);
            self::reset();
        }
    }

    /**
     * Parse through the request and evaluate raw HTTP header data. This function
     * will populate the PHP server environment the way one would normally expect
     * it to be via mod_php or FastCGI.
     *
     * @param string $request  An associative array containing request data
     * @return bool
     */
    public function set_environment($request)
    {
        if ($request['method'] == 'GET') {
            global $_GET;
            $_GET = $request['params'];
        } else if ($request['method'] == 'PUT' || $request['method'] == 'POST') {
            global $_POST;
            $_POST = $request['params'];
        }
        global $_SERVER;
        $_SERVER['REQUEST_METHOD'] = $request['method'];
        $_SERVER['REQUEST_URI'] = $request['uri'];
        $_SERVER['SERVER_PROTOCOL'] = $request['protocol'];
        $_SERVER['REMOTE_ADDR'] = $request['remote_addr'];
        $_SERVER['REMOTE_PORT'] = $request['remote_port'];
        return true;
    }

    /**
     * reset
     *
     * Performs house cleanup for each request. If this is not called after processing each
     * request, it would be possible for POST / GET data from previous API requests to carry
     * over to subsequent requests, until the same data was passed in again.
     *
     * @return bool
     */
    public function reset()
    {
        global $_SERVER;
        foreach (array(
            'REQUEST_METHOD',
            'REQUEST_URI',
            'SERVER_PROTOCOL',
            'REMOTE_ADDR',
            'REMOTE_PORT') as $index) {
            if (array_key_exists($index, $_SERVER)) {
                unset($_SERVER[$index]);
            }
        }

        global $_GET;
        if (isset($_GET)) {
            foreach ($_GET as $k => $v) {
                unset($_GET[$k]);
            }
        }

        global $_POST;
        if (isset($_POST)) {
            foreach ($_POST as $k => $v) {
                unset($_POST[$k]);
            }
        }
    }
}

?>
