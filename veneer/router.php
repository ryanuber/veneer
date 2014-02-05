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
 */

namespace veneer;

/**
 * router
 *
 * Performs very basic, straightforward routing. There is no magic here.
 * Route priorities are definable by ordering them from greatest-to-least
 * priority within the endpoints. This class simply stacks up route
 * information, and provides a single call to select the most suitable
 * route for the request.
 */
class router
{
    /**
     * @var array  A collection of routes defined by the endpoint
     */
    private $routes = array();

    /**
     * Adds a route to the stack for later evaluation.
     *
     * @param string $http_method  The HTTP method
     * @param string $route  The textual route representation
     * @param mixed $data  The route data
     * @return bool
     */
    public function add_route($http_method, $route, $data)
    {
        array_push($this->routes, array($http_method, $route, $data));
    }

    /**
     * Route selector. This method traverses through the stacked routes
     * in the current instance and populates the $callback_fn and
     * $callback_params variables with information for the call class to
     * execute on. The callback_fn and callback_params variables should be
     * passed by reference and will be modified in this function if a
     * route matches the request.
     *
     * @param string $requested  The URI requested
     * @param string $callback_data  The route data to return
     * @param array $callback_params  Parsed query parameters to pass in
     * @return bool  true on route match, false on no matches
     */
    public function select_route($requested, &$callback_data, &$callback_params)
    {
        $method = trim($requested, '/');
        $request = explode('/', $method);
        foreach ($this->routes as $route) {
            list($http_method, $route, $data) = $route;
            $route = str_replace('*', '(.*)', trim($route, '/'));
            $params = array();
            $count = 0;
            foreach (explode('/', $route) as $part) {
                if (preg_match('~^\:~', $part) && array_key_exists($count, $request)) {
                    $key = preg_replace('~^:~', '', $part);
                    $params[$key] = urldecode($request[$count]);
                    $route = str_replace($part, '([^/]+)', $route);
                }
                $count++;
            }
            if (preg_match(sprintf('~^%s$~', $route), $method)) {
                $callback_data = $data;
                $callback_params = $params;
                return true;
            }
        }
        return false;
    }
}

?>
