<?php
class test_serialize_output extends PHPUnit_Framework_TestCase
{
    public function test_serialize_output_array()
    {
        $serialized = 'a:1:{s:9:"root_item";a:2:{s:9:"sub_item1";s:6:"value1";s:9:"sub_item2";s:6:"value2";}}';
        $array = array('root_item' => array('sub_item1' => 'value1', 'sub_item2' => 'value2'));
        $this->assertEquals(
            $serialized,
            \veneer\output\handler\serialize::output_arr($array)
        );
    }

    public function test_serialize_output_string()
    {
        $serialized = 's:16:"This is a string";';
        $string = 'This is a string';
        $this->assertEquals(
            $serialized,
            \veneer\output\handler\serialize::output_str($string)
        );
    }

    public function test_serialize_headers()
    {
        $headers = array();
        foreach (\veneer\output\handler\serialize::headers() as $header) {
            array_push($headers, strtolower($header));
        }
        $this->assertTrue(in_array(
            'content-type: text/plain',
            $headers
        ));
    }
}
