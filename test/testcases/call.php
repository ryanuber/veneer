<?php
class test_call extends PHPUnit_Framework_TestCase
{
    public function test_invoke()
    {
        require 'test_endpoint.php';
        $call = new \veneer\endpoint\test_endpoint\v1;
        $response = new \veneer\http\response;
        $response->set_output_handler('plain');
        $call->invoke('/my/route', $response);

        $this->assertEquals('This is the response', $response->get_body());
        $this->assertEquals(200, $response->get_status());
    }
}
