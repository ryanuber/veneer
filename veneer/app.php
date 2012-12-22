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

namespace veneer;

/**
 * app
 *
 * Main application class to call to begin executing the veneer framework.
 * A call to any of these class methods should be the last statement in
 * your PHP code, because the run() method will send encoded output.
 */
class app
{
    /**
     * Runs through defined API routines once. This call should be executed
     * once per request.
     *
     * @return bool
     */
    public static function run()
    {
        try {
            $response = new \veneer\http\response;

            $path = \veneer\util::request_path();
            $endpoint_version = array_shift($path);
            $endpoint_name    = array_shift($path);

            $parts = explode('.', $endpoint_name);
            if (count($parts) == 2) {
                $encoding = array_pop($parts);
                $endpoint_name = implode('.', $parts);
                $response->set_encoding($encoding);
            }

            $endpoint_version = \veneer\util::version('class', $endpoint_version);
            $class = sprintf('\veneer\endpoint\%s\%s', $endpoint_name, $endpoint_version);

            if (class_exists($class)) {
                $instance = new $class;
                $instance->invoke('/'.implode('/', $path), &$response);
                $response->send($endpoint_name, $endpoint_version);
            } else {
                $return = array(
                    'error' => 'Requested endpoint is not implemented',
                    'endpoints' => \veneer\util::get_endpoints()
                );
                $response->set($return, 501);
                $response->send();
            }
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Includes the socket listener class and instantiates it, creating an HTTP
     * server instance that can serve requests to the run() method.
     *
     * @param string $bind_addr  The inet address to bind to
     * @param integer $bind_port  The TCP port to bind on
     * @return bool
     */
    public static function listen($bind_addr='0.0.0.0', $bind_port='8080')
    {
        require_once \veneer\util::path_join('http', 'server.php');
        require_once \veneer\util::path_join('http', 'request.php');
        new \veneer\http\server($bind_addr, $bind_port);
    }
}

?>