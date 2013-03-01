<?php
class test_veneer_app extends PHPUnit_Framework_TestCase
{
    public function test_defaults_set_and_get()
    {
        $this->assertTrue(\veneer\app::set_default('mysetting', 'myvalue'));
        $this->assertEquals('myvalue', \veneer\app::get_default('mysetting'));
    }

    public function test_get_nonexistent_default()
    {
        $this->assertEquals(null, \veneer\app::get_default('oogabooga'));
    }

    public function test_run()
    {
        $result = true;
        try {
            ob_start();
            \veneer\app::run();
            $output = ob_get_clean();
        } catch (\Exception $e) {
            $result = false;
        }
        $this->assertTrue($result);
    }

    public function test_listen()
    {
        $mock = $this->getMock('\veneer\app', array('listen'));
        $mock->expects($this->once())->method('listen')->with($this->equalTo('0.0.0.0'), $this->equalTo(8080));
        $subject = new Subject;
        $subject->attach($mock);
        $subject::listen();
    }
}
