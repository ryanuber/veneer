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

namespace veneer\output\handler;

/**
 * Returns the response data as HTML by sending the appropriate headers.
 * This outputter is a near-identical copy of the plain outputter with
 * the only real difference being the MIME type sent in the response.
 */
class html implements \veneer\output\str
{
    /**
     * Output string data
     *
     * @param mixed $data  String or array data to output
     * @return string
     */
    public static function output_str($data)
    {
        return (string)$data;
    }

    /**
     * Sets headers associated with this output type
     *
     * @return array
     */
    public static function headers()
    {
        return array('Content-Type: text/html');
    }
}

?>
