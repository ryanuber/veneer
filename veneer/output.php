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
 * Provides some insurance that any extending outputters will implement
 * ouptut and header settings in a consistent way.
 */
interface output
{
    /**
     * Return an array of headers to set for this output type
     *
     * @return array
     */
    public static function headers();
}

/* EOF */
?>
