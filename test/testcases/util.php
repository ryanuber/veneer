<?php
class test_util extends PHPUnit_Framework_TestCase
{
    public function test_path_join()
    {
        $this->assertEquals(
            'path/to/some/file',
            \veneer\util::path_join(array('path', 'to', 'some', 'file'))
        );
    }

    public function test_version_munger()
    {
        $this->assertEquals('v5.9', \veneer\util::version('number', 'v5_9'));
        $this->assertEquals('v5_9', \veneer\util::version('class', 'v5.9'));
        $this->assertFalse(\veneer\util::version('oogabooga', 'v5.9'));
    }
}
