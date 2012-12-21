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
 * request
 *
 * Supporting functions for handling HTTP requests at the message
 * layer. This class keeps the parsing logic out of the main server
 * class and contains only static methods for passing in raw data
 * and getting something usable back for the server.
 */
class request
{
    /**
     * Handles HTTP message data by converting a textual message
     * representation into an associative array containing the data
     * one would likely need to get from it.
     *
     * @param string $request  The HTTP message data
     * @return array
     */
    public static function from_message($request)
    {
        if ($request == '') {
            return false;
        }
        $result = array();
        $parts = explode("\r\n\r\n", $request);
        $headers = explode("\r\n", array_shift($parts));
        list(
            $result['method'],
            $query,
            $result['protocol']
        ) = preg_split('/\s+/', array_shift($headers));
        $qsstart = strpos($query, '?') ? strpos($query, '?') : strlen($query);
        $result['query_string'] = ltrim(substr($query, $qsstart), '?');
        $result['uri'] = rtrim(substr($query, 0, $qsstart), '?');
        $result['body'] = implode("\r\n\r\n", $parts);
        $result['headers'] = array();
        foreach ($headers as $header) {
            /**
             * Per RFC2616 section 4.2 (Message Headers):
             * - The field value MAY be preceded by any amount of LWS,
             *   though a single SP is preferred
             * - Field names are case-insensitive.
             */
            list($key, $val) = preg_split('/:(\s+)?/', $header);
            $result['headers'][strtolower($key)] = $val;
        }
        $params = array();
        if ($result['query_string'] != '') {
            parse_str($result['query_string'], $params);
        } else {
            parse_str($result['body'], $params);
        }
        $result['params'] = $params;
        return $result;
    }
}

?>
