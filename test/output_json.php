<?php
class test_json_output extends PHPUnit_Framework_TestCase
{
    public function test_json_encoder_exists()
    {
        $this->assertTrue(function_exists('json_encode'));
    }
}
