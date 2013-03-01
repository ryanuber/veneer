<?php
class test_output_handler_plain extends PHPUnit_Framework_TestCase
{
    public function test_plain_output_string()
    {
        $this->assertEquals(
            'This is a plain string',
            \veneer\output\handler\plain::output_str('This is a plain string')
        );
    }

    public function test_plain_headers()
    {
        $headers = array();
        foreach (\veneer\output\handler\plain::headers() as $header) {
            array_push($headers, strtolower($header));
        }
        $this->assertTrue(in_array(
            'content-type: text/plain',
            $headers
        ));
    }
}
