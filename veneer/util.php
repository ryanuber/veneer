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
 * util
 *
 * Provides a handful of commonly used functions to make other API code
 * cleaner and more consistent. All methods contained within this class
 * are static, so there is no need to instantiate an instance of util.
 */
class util
{
    /**
     * Construct a filesystem path using the system-defined path separator
     *
     * @param string $arg  Textual representation(s) of a relative directory
     * @return string
     */
    public static function path_join()
    {
        $result = '';
        foreach (func_get_args() as $arg) {
            $result .= ($result==''?'':DIRECTORY_SEPARATOR).$arg;
        }
        return $result;
    }

    /**
     * list_dir - List contents of a directory
     *
     * This compact function simply creates a way to list files in a
     * directory without having to rewrite the code that excludes the current
     * working directory and its parent each time you want to do a scandir().
     *
     * @param string $path  The path to the directory you want to list
     * @return mixed  Array on success, false if directory doesn't exist
     */
    public static function list_dir($path)
    {
        $path = self::path_join(dirname(__FILE__), $path);
        if (!is_dir($path)) {
            return false;
        }
        $result = array();
        foreach (scandir($path) as $item) {
            $item != '.' && $item != '..' && array_push($result, self::path_join($path, $item));
        }
        return $result;
    }

    /**
     * include_dir - Loop through a directory and source all php files
     *
     * This function will look at all files in a given directory, make sure that they
     * are files (not directories), and include them if they have a .php extension.
     * It also supports a mechanism to handle cases where a directory is found instead
     * of a file by allowing the user to specify a static file name to look for within
     * the directory.
     *
     * @param string $dir  The directory to scan
     * @param string $index  If a directory is found, search it for this file
     *
     * @return bool
     */
    public static function include_dir($dir, $index='')
    {
        if ($files = self::list_dir($dir)) {
            foreach ($files as $file) {
                is_file($file) && substr($file, -4) == '.php' && require_once $file;
            }
        }
    }

    /**
     * Fetch path information from the request URL
     *
     * @param bool $trim  If true, trims off method and version information
     *                    which results in a path similar to what a route
     *                    would look like.
     * @return array
     */
    public static function request_path($trim=false)
    {
        $result = array();
        $request = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null;
        strpos($request, '?') === false || $request = substr($request, 0, strpos($request, '?'));
        foreach (explode('/', $request) as $i) {
            $i != '' && $result[] = $i;
        }
        return $trim ? array_slice($result,2) : $result;
    }

    /**
     * Fetch request parameters, if any, from the request.
     *
     * @param string $name  The name of the parameter to fetch (optional)
     * @return array
     */
    public static function request_params($param=null)
    {
        $result = array();
        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return $result;
        }
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $result = $_GET;
                break;
            case 'POST':
                $result = $_POST;
                break;
            case 'PUT':
                parse_str(file_get_contents('php://input'), $result);
                if (count($result) == 0 && isset($_POST) && count($_POST) > 0) {
                    $result = $_POST;
                }
                break;
        }
        if (array_key_exists('QUERY_STRING', $_SERVER)) {
            parse_str($_SERVER['QUERY_STRING'], $query_string_params);
            $result = array_replace($query_string_params, $result);
        }
        if (!is_null($param)) {
            return array_key_exists($param, $result) ? $result[$param] : '';
        } else {
            return $result;
        }
    }

    /**
     * Convert an endpoint class name to a version number, or vice versa
     *
     * @param string $in  The endpoint class name or version number
     * @return mixed  String on success, false on error
     */
    public static function version($format, $in)
    {
        if ($format == 'number') {
            return str_replace('_', '.', $in);
        } else if ($format == 'class') {
            return str_replace('.', '_', $in);
        } else {
            return false;
        }
    }

    /**
     * Returns a list of API endpoints available for execution organized by the
     * endpoint versions.
     *
     * @return array
     */
    public static function get_endpoints()
    {
        $endpoints = array();
        foreach (get_declared_classes() as $declared) {
            $parts = explode('\\', $declared);
            if ($parts[0] == 'veneer' && $parts[1] == 'endpoint') {
                $version = self::version('number', $parts[3]);
                $endpoints[$version][] = $parts[2];
            }
        }
        return $endpoints;
    }

    /**
     * Iterate over all discovered endpoints, gathering their route details for
     * each of get, post, put, and delete methods. This function may be deleted
     * after implementing the OPTIONS method handler.
     *
     * @return array
     */
    public static function get_documentation()
    {
        $docs = array();
        foreach (self::get_endpoints() as $version => $endpoints) {
            foreach ($endpoints as $endpoint) {
                $class = sprintf('\veneer\endpoint\%s\%s', $endpoint, self::version('class', $version));
                $instance = new $class;
                $docs[$endpoint][$version] = $instance->retrieve_detail();
            }
        }
        return $docs;
    }

    /**
     * Convert a header name to a suitably-matching _SERVER index name, as typically
     * set by PHP. This is simple string manipulation.
     *
     * @param string $name  The header name to convert
     * @return string
     */
    public static function header_to_index($name)
    {
        return 'HTTP_'.strtoupper(str_replace('-', '_', $name));
    }
}

/* EOF */
?>
