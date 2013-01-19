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

namespace veneer\encoding;

/**
 * Provides XML data encoding. Currently does not do any pretty-printing as
 * not all PHP distributions provide DOMDocument, which is typically what
 * one would use to do the indentation.
 */
class xml implements
    \veneer\prototype\encoding,
    \veneer\prototype\encoding_array,
    \veneer\prototype\encoding_string
{
    /**
     * to_xml - Convert (on best-effort terms) and associative array to XML
     *
     * This method attempts to convert an array into an XML string. There are
     * numerous limitations in doing so, since XML is typically expressed in
     * ways that are just not 1:1 translatable from an array. For instance,
     * arrays can be referenced by individual index, but in XML, there may
     * be many indices with the same index.
     *
     * @param array $data  Array of data to encode into XML
     * @object $xml  Optional child SimpleXML object to parse to (recursion)
     *
     * @return string
     */
    public static function to_xml(array $data, $xml=null)
    {
        if (is_null($xml)) {
            $xml = simplexml_load_string('<?xml version="1.0"?><result></result>');
        }

        foreach ($data as $k => $v) {
            is_numeric($k) && $k = 'item'.(string)$k;
            $key = '';
            for ($i=0;$i<strlen($k);$i++) {
                (ctype_alpha($k[$i]) || $k[$i] == '_') && $key .= $k[$i];
            }
            $k = $key;

            if (is_array($v)) {
                $n = $xml->addChild($k);
                self::to_xml($v, $n);
            } else  {
                $v = htmlentities($v);
                $xml->addChild($k,$v);
            }
        }
        return $xml->asXML();
    }

    /**
     * Encode string data
     *
     * @return string
     */
    public static function encode_string($data)
    {
        return self::to_xml($data);
    }

    /**
     * Encode array data
     *
     * @return array
     */
    public static function encode_array($data)
    {
        return self::to_xml($data);
    }

    /**
     * Sets headers associated with this encoding type
     *
     * @return array
     */
    public static function headers()
    {
        return array('Content-Type: text/xml; charset=utf-8');
    }
}

?>
