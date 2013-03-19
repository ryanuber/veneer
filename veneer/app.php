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
 * your PHP code, because the run() method will send output.
 */
class app
{
    /**
     * @var array  A simple data structure to hold various default settings
     */
    private static $defaults = array(
        'output_handler' => 'json',
        'output_handler_param' => 'format'
    );

    /**
     * Set a default configuration value
     *
     * @param string $name  The name of the configuration parameter
     * @param string $value  The value of the configuration parameter
     * @return bool
     */
    public static function set_default($name, $value)
    {
        return (self::$defaults[$name] = $value) ? true : false;
    }

    /**
     * Retrieve the default value of a configuration parameter
     *
     * @param string $name  The name of the configuration parameter
     * @return mixed
     */
    public static function get_default($name)
    {
        return array_key_exists($name, self::$defaults) ? self::$defaults[$name] : null;
    }

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
            $request_params   = \veneer\util::request_params();

            $endpoint_version = \veneer\util::version('class', $endpoint_version);
            $class = sprintf('\veneer\endpoint\%s\%s', $endpoint_name, $endpoint_version);

            if (class_exists($class)) {
                $instance = new $class;
                $instance->set_request_params($request_params);
                $instance->invoke('/'.implode('/', $path), $response);
            } else {
                $handler_param = self::get_default('output_handler_param');
                $default_handler = self::get_default('output_handler');
                $response->configure_handler($handler_param, $default_handler, $request_params);
                $return = array(
                    'error' => 'No such endpoint',
                    'endpoints' => \veneer\util::get_endpoints()
                );
                $response->set($return, 404);
            }
            $response->send();
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
