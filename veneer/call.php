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
 * call
 *
 * Provides a base class for all API endpoints to extend. This eliminates
 * the need for each API endpoint to implement its own route handling and
 * gives us a way of ensuring that each endpoint at least implements a
 * few basic class methods.
 */
abstract class call
{
    /**
     * @var array  The response object for the request
     */
    public $response = null;

    /**
     * @var array  HTTP GET routes
     */
    public $get = array();

    /**
     * @var array  HTTP POST routes
     */
    public $post = array();

    /**
     * @var array  HTTP PUT routes
     */
    public $put = array();

    /**
     * @var array  HTTP DELETE routes
     */
    public $delete = array();

    /**
     * Discover route matches and invoke any corresponding methods found.
     * Handles route parsing and route parameters using some simple
     * regular expression pattern matching and function calling.
     *
     * @param string $method  The URL bit to match routes against
     * @param \veneer\http\response $response  Response object
     * @return bool
     */
    public function invoke($method, \veneer\http\response &$response)
    {
        $method = trim($method, '/');
        $request = explode('/', $method);
        $this->response = &$response;
        $router = new \veneer\router;
        $request_method = array_key_exists('REQUEST_METHOD', $_SERVER) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';

        foreach (array('get', 'post', 'put', 'delete') as $http_method) {
            if ($http_method == $request_method) {
                if (isset($this->$http_method)) {
                    foreach ($this->$http_method as $route => $data) {
                        $router->add_route($http_method, $route, $data);
                    }
                }
            }
        }

        $data = null;
        $params = array();
        $router->select_route($method, $data, $params);
        if (is_array($data) && array_key_exists('output_handler_param', $data)) {
            $output_handler_param = $data['output_handler_param'];
        } else {
            $output_handler_param = \veneer\app::get_default('output_handler_param');
        }
        foreach (\veneer\util::request_params() as $name => $value) {
            if ($name == $output_handler_param) {
                $this->response->set_output_handler($value);
            }
        }
        if (is_array($data) && array_key_exists('output_handler', $data)) {
            if ($this->response->get_output_handler() == null) {
                $this->response->set_output_handler($data['output_handler']);
            }
        }
        $params = array_merge(\veneer\util::request_params(), $params);
        if ($fn = self::validate($data, $params)) {
            if (method_exists($this, $fn)) {
                call_user_func(array($this, $fn), $params);
            }
        }

        if (!$this->response->is_set()) {
            if ($request_method == 'options') {
                $this->response->set($this->retrieve_detail(), 200);
            } else {
                $this->response->set('Incomplete response data returned by endpoint', 500);
            }
        }
    }

    /**
     * Validate a basic constraint on a parameter. Constratints are simply regular
     * expression matching results. If the value of the parameter matches the
     * regular expression, the constraint is satisfied.
     *
     * @param string $constraint  The regular expression to math
     * @param mixed $value  The value of the parameter
     * @return bool
     */
    private function validate_constraint($constraint, $value)
    {
        return preg_match('~'.$constraint.'~', $value) == 1;
    }

    /**
     * Validate the value of a parameter in a more free-form way by allowing the
     * developer to specify an arbitrary fuction for validation. The framework
     * automatically passes in the value of the parameter, and if the user funciton
     * returns true, this check will return true. Any other return value will be
     * treated as a failure.
     *
     * @param string $fn  The validation function to call
     * @param mixed $value  The value to validate
     * @return bool
     */
    private function validate_function($fn, $value)
    {
        if (method_exists($this, $fn)) {
            return call_user_func(array($this, $fn), $value) === true;
        } else {
            return false;
        }
    }

    /**
     * Validate any defined route detail. Routes do not need to contain detail - in fact
     * they can be just a single key => value pair per route and that is sufficient.
     * If a developer chooses, they may add route details in array format which can be
     * used for input and request validation as well as documentation.
     *
     * @param mixed $data  The data associated with the route
     * @param array $params  The keys/values collected to validate
     * @return mixed  String function name on success, false on validation error
     */
    private function validate($data, $params)
    {
        if (is_array($data)) {
            if (array_key_exists('parameters', $data)) {
                is_array($data['parameters']) && $paramdata = $data['parameters'];
            }
        }
        isset($paramdata) || $paramdata = array();
        $invalid = array();
        foreach ($paramdata as $key => $val) {
            if (array_key_exists($key, $params)) {
                if (array_key_exists('constraint', $val)) {
                    if (!self::validate_constraint($val['constraint'], $params[$key])) {
                        array_push($invalid, $key);
                        continue;
                    }
                }
                if (array_key_exists('validate_fn', $val)) {
                    if (!self::validate_function($val['validate_fn'], $params[$key])) {
                        array_push($invalid, $key);
                        continue;
                    }
                }
            } else {
                if (array_key_exists('required', $val) && $val['required']) {
                    $this->response->set('Required parameter "'.$key.'" missing', 400);
                    return false;
                }
            }
        }
        if (count($invalid) > 0) {
            $this->response->set('Invalid value(s) for: '.implode(', ', $invalid), 400);
            return false;
        }
        return is_array($data) ? $data['function'] : $data;
    }

    /**
     * Returns all defined routing details for this endpoint in an associative
     * array organized by HTTP request type.
     *
     * @return array
     */
    public function retrieve_detail()
    {
        return array(
            'get' => $this->get,
            'post' => $this->post,
            'put' => $this->put,
            'delete' => $this->delete
        );
    }
}

?>
