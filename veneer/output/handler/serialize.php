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
