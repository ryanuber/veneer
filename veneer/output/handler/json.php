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
 * Provides JSON serialized output. Indents data so that humans can easily
 * read data for debugging purposes.
 */
class json implements
    \veneer\output\arr,
    \veneer\output\str
{
    /**
     * jsonpp - Pretty print JSON data
     *
     * In versions of PHP < 5.4.x, the json_encode() function does not yet provide a
     * pretty-print option. In lieu of forgoing the feature, an additional call can
     * be made to this function, passing in JSON text, and (optionally) a string to
     * be used for indentation.
     *
     * @param string $json  The JSON data, pre-encoded
     * @param string $istr  The indentation string
     * @return string
     */
    public static function jsonpp($json, $istr='  ')
    {
        $result = '';
        for ($p=$q=$i=0; isset($json[$p]); $p++) {
            $json[$p] == '"' && ($p>0?$json[$p-1]:'') != '\\' && $q=!$q;
            if (strchr('}]', $json[$p]) && !$q && $i--) {
                strchr('{[', $json[$p-1]) || $result .= "\n".str_repeat($istr, $i);
            }
            $result .= $json[$p];
            if (strchr(',{[', $json[$p]) && !$q) {
                $i += strchr('{[', $json[$p]) === false ? 0 : 1;
                strchr('}]', $json[$p+1]) || $result .= "\n".str_repeat($istr, $i);
            }
        }
        return $result;
    }

    /**
     * Output string data
     *
     * @param string $data  The data to output
     * @return array
     */
    public static function output_str($data)
    {
        return self::jsonpp(json_encode($data));
    }

    /**
     * Output array data
     *
     * @param array $data  The data to output
     * @return array
     */
    public static function output_arr(array $data)
    {
        return self::jsonpp(json_encode($data));
    }

    /**
     * Sets headers associated with this encoding type
     *
     * @return array
     */
    public static function headers()
    {
        return array('Content-Type: application/json');
    }
}

?>
