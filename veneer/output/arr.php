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

namespace veneer\output;

/**
 * Providers for array data serialization must implement this interface
 */
interface arr extends \veneer\output
{
    /**
     * Encode array data
     *
     * @param array $data  Array data to serialize
     * @return string
     */
    public static function output_arr(array $data);
}

/* EOF */
?>
