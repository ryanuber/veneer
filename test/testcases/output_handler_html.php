<?php
class test_output_handler_html extends PHPUnit_Framework_TestCase
{
    public function test_html_output_string()
    {
        $this->assertEquals(
            'This is a plain string',
            \veneer\output\handler\html::output_str('This is a plain string')
        );
    }

    public function test_html_headers()
    {
        $headers = array();
        foreach (\veneer\output\handler\html::headers() as $header) {
            array_push($headers, strtolower($header));
        }
        $this->assertTrue(in_array(
            'content-type: text/html',
            $headers
        ));
    }
}
