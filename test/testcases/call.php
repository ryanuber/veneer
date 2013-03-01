<?php
class test_call extends PHPUnit_Framework_TestCase
{
    public function test_validate_constraint()
    {
        $this->assertTrue(\veneer\call::validate_constraint('^[a-z]+$', 'thisIsATest'));
        $this->assertFalse(\veneer\call::validate_constraint('^[a-z]+$', '_oopsThisDoesntValidate_'));
    }
}
