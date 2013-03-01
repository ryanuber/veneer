<?php
namespace veneer\endpoint\test_endpoint;

class v1 extends \veneer\call
{
    public $get = array('/my/route' => 'myfunction');
    public function myfunction($args)
    {
        return $this->response->set('This is the response', 200);
    }
}
