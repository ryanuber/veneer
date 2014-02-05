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
