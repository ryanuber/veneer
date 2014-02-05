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
 * Providers for string data must implement this interface
 */
interface str extends \veneer\output
{
    /**
     * Output string data
     *
     * @param string $data  The string data to output
     * @return string
     */
    public static function output_str($data);
}

/* EOF */
?>
