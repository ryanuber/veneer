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
