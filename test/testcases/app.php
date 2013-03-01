<?php
class test_veneer_app extends PHPUnit_Framework_TestCase
{
    public function test_defaults_set_and_get()
    {
        $this->assertTrue(\veneer\app::set_default('output_handler', 'some_handler'));
        $this->assertEquals(\veneer\app::get_default('output_handler'), 'some_handler');
    }

    public function test_run()
    {
        $result = true;
        try {
            \veneer\app::run();
        } catch (\Exception $e) {
            $result = false;
        }
        $this->assertTrue($result);
    }
}
