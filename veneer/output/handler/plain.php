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
 * Returns plain text data. This is useful for things that return data
 * in proprietary or otherwise unimplemented formats.
 */
class plain implements \veneer\output\str
{
    /**
     * Output string data
     *
     * @param string $data  String data to output
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
        return array('Content-Type: text/plain');
    }
}

?>
