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
 * Provides PHP-serialized data. This would only be useful for remote PHP
 * applications consuming these API's. The benefit is that there is no need
 * for the JSON library. This class is here more to demonstrate the
 * the simplicity with wich one could implement an output method.
 */
class serialize implements
    \veneer\output\str,
    \veneer\output\arr
{
    /**
     * Output string data
     *
     * @param string $data  String data to output
     * @return string
     */
    public static function output_str($data)
    {
        return serialize($data);
    }

    /**
     * Output array data
     *
     * @param array $data  Array data to output
     * @return string
     */
    public static function output_arr(array $data)
    {
        return serialize($data);
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
