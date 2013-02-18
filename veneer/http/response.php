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

namespace veneer\http;

/**
 * response - Provides response encapsulation
 *
 * This class abstracts sending a response to the client. Flow control becomes
 * very difficult when different headers are set at different times by different
 * plugins, encoders, appenders, etc. Printing output becomes an obstacle if you
 * have no way of knowing if the headers were already sent, if you have already
 * printed a response, etc.
 *
 * Previously the way this was handled in this framework was, a response would
 * be sent, and immediately after, a call was made to either die() or exit().
 * This method does not work if you then try to wrap an API call into the same
 * PHP thread by use of the include* or require* functions.
 *
 * This newer way of response handling essentially creates a response stack,
 * containing all of the data and information about how to return it.
 */
class response
{
    /**
     * @var integer  The HTTP status to send in the response
     */
    private $status;

    /**
     * @var mixed  The response body, can be either text or an array.
     */
    private $body;

    /**
     * @var string  The encoding mechanism to use. An encoder by this name must be installed.
     */
    private $encoding;

    /**
     * @var bool  A true/false value indicating whether or not to send raw headers.
     */
    private $send_raw_headers = false;

    /**
     * @var array  A single-dimension array containing headers to be sent.
     */
    private $headers = array();

    /**
     * @var bool  Response set boolean indicator
     */
    private $response_set;

    /**
     * This function appends to a local array instance of headers. Before appending,
     * it will iterate over all previously set headers to check if they define an
     * identical parameter. If a duplicate is found, the previous header is unset,
     * and the newer value takes over.
     *
     * @param string $data  The header data to set
     * @return bool
     */
    public function set_header($data)
    {
        if (!is_string($data)) {
            return false;
        }

        $delim = substr($data, 0, 5) == 'HTTP/' ? '/' : ':';
        $comp  = substr($data, 0, strpos($data, $delim));
        $count = 0;
        foreach ($this->headers as $header) {
            if (substr($header, 0, strpos($header, $delim)) == $comp) {
                unset($this->headers[$count]);
            }
        }
        return array_push($this->headers, $data);
    }

    /**
     * Setter function for the response body
     *
     * @param mixed $body  String, integer, or array containing the response body
     * @param integer $status  The HTTP response status
     * @return bool
     */
    public function set($body='', $status=500)
    {
        $this->response_set = true;
        $this->set_body($body);
        $this->set_status($status);
    }

    /**
     * Setter function for just the response body.
     *
     * @param mixed $data  String, integer, or array containing the response body
     * @return bool
     */
    public function set_body($data='')
    {
        $this->response_set = true;
        return $this->body = $data;
    }

    /**
     * Setter function for just the HTTP response status
     *
     * @param integer $status  The HTTP response status
     * @return bool
     */
    public function set_status($status=500)
    {
        if (!is_int($status)) {
            return false;
        }
        $this->response_set = true;
        return $this->status = $status;
    }

    /**
     * Setter function for the response encoding handler
     *
     * @param string $encoding  Name of the encoder to use
     * @return bool
     */
    public function set_encoding($encoding)
    {
        return $this->encoding = is_null($encoding) ? null : strtolower($encoding);
    }

    /**
     * Enable or disable detailed responses
     *
     * @param bool $value  True/false for enabled/disabled, respectively.
     * @return bool
     */
    public function show_detail($value)
    {
        return self::$response_detail = (bool)$value;
    }

    /**
     * Add response detail to the response body
     *
     * @return bool
     */
    public function add_detail($endpoint_name='', $endpoint_version='')
    {
        $response = array();
        if ($endpoint_name != '' && $endpoint_version != '') {
            $response['endpoint'] = $endpoint_name;
            $response['version']  = \veneer\util::version('number', $endpoint_version);
        }
        $response['status'] = $this->status;
        $response['response'] = $this->body;
        self::set_body($response);
    }

    /**
     * Getter function for the response body
     *
     * @return mixed  String, integer, or array
     */
    public function get_body()
    {
        return $this->body;
    }

    /**
     * Getter function for the HTTP response status
     *
     * @return integer
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * Returns a list of headers that are set. Two loops are required so that we can
     * return the HTTP status header first, if set. This way if we have more than one
     * function that needs to fetch a list of headers, this logic does not need to be
     * re-implemented each time.
     *
     * @return array
     */
    public function get_headers()
    {
        $result = array();
        foreach ($this->headers as $header) {
            substr($header, 0, 5) == 'HTTP/' && array_push($result, $header);
        }
        foreach ($this->headers as $header) {
            substr($header, 0, 5) == 'HTTP/' || array_push($result, $header);
        }
        return $result;
    }

    /**
     * Getter function for the encoding type
     *
     * @return string
     */
    public function get_encoding()
    {
        return $this->encoding;
    }

    /**
     * is_set - Check if the response has been set or not.
     *
     * Certain parts of the API framework need to determine whether or not a
     * response has already been set. Rather than checking the status and body
     * individually, check this boolean value instead.
     *
     * @return bool
     */
    public function is_set()
    {
        return $this->response_set;
    }

    /**
     * send - Send the response to the client
     *
     * This function provides a simple way for API callers to print their data
     * once gathered from the database. You simply pass the array of results
     * that you fetch from the database, or the strings 'success' or 'failure'
     * if you want to print just a status message (for write operations such as
     * UPDATE's or INSERT's), and the data will be formatted automatically in
     * your preferred format based on GET and POST variables.
     *
     * @param string $endpoint_name  The name of the endpoint
     * @param string $endpoint_version  The version of the endpoint
     * @return void
     */
    public function send($endpoint_name='', $endpoint_version='')
    {
        /**
         * Don't cache anything. In the future, perhaps a "max-age" could be
         * configurable. This will become more important once a full HATEOAS
         * implementation makes it into this API.
         */
        self::set_header('Cache-Control: no-cache');

        /**
         * If no encoding was set by the calling API (usually if a response was set
         * before API class invocation), then try the query parameters.
         */
        if (!isset($this->encoding) || $this->encoding == '') {
            self::set_encoding(\veneer\app::get_default('encoding'));
        }

        $class = '\veneer\encoding\\'.$this->encoding;
        if (class_exists($class) && in_array('veneer\prototype\encoding', class_implements($class))) {
            if (in_array('veneer\prototype\encoding_array', class_implements($class))) {
                \veneer\app::get_default('response_detail') && $this->add_detail($endpoint_name, $endpoint_version);
            }
            if (is_string($this->body) || is_int($this->body)) {
                if (in_array('veneer\prototype\encoding_string', class_implements($class))) {
                    $output = $class::encode_string($this->body);
                } else {
                    self::set('Encoder "'.$this->encoding.'" cannot handle string data', 500);
                    self::set_encoding(null);
                }
            } else {
                if (in_array('veneer\prototype\encoding_array', class_implements($class))) {
                    $output = $class::encode_array($this->body);
                } else {
                    self::set('Encoder "'.$this->encoding.'" cannot handle array data', 500);
                    self::set_encoding(null);
                }
            }

            if (is_null(self::get_encoding())) {
                $output = $this->body;
            }

            /**
             * Different encoders probably have different MIME types, and if implemented
             * correctly, this call should set those headers for this request.
             */
            foreach ($class::headers() as $header) {
                self::set_header($header);
            }
        } else {
            self::set('FATAL: No encoders found for "'.$this->encoding.'"', 500);
            $output = $this->body;
        }

        self::http_status($this->status);

        foreach (self::get_headers() as $header) {
            if (!$this->send_raw_headers) {
                @header($header);
            }
            if ($this->send_raw_headers || count(headers_list()) == 0) {
                $this->send_raw_headers = true;
                print $header . "\r\n";
            }
        }

        /**
         * If sending raw headers, add a few standard and useful headers that are normally
         * added by your web server layer.
         */
        if ($this->send_raw_headers) {
            print "Date: " . gmdate('D, d M Y H:i:s T') . "\r\n".
                  "Content-Length: " . strlen($output) . "\r\n".
                  "Connection: Close\r\n";
        }

        print ($this->send_raw_headers ? "\r\n" : '') . $output;
    }

    /** 
     * http_status - Set HTTP response status
     *
     * Function to provide short-hand access to setting HTTP status codes. These are
     * important to the REST API because clients will check HTTP status to determine
     * whether or not an operation was successful, and what type of error (if any)
     * occurred while processing the request.
     *
     * @param integer $statusCode  The HTTP status code to set
     * @return bool
     */
    public function http_status($status_code=200)
    {
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Request Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gatway Time-out',
            505 => 'HTTP Version Not Supported'
        );  

        $protocol = isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1';
        $version = substr($protocol, strpos($protocol, '/')+1);
        if ($version != '1.0' && $version != '1.1') {
            $status_code = 505;
            $protocol = 'HTTP/1.1';
        }
        $code = array_key_exists($status_code, $codes)?$status_code:500;
        return self::set_header("{$protocol} {$code} {$codes[$code]}");
    }   
}

?>
