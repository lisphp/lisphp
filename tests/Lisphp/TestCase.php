<?php

class Lisphp_TestCase extends PHPUnit_Framework_TestCase
{
    public function assertType($class, $instance)
    {
        $this->assertTrue($instance instanceof $class);
    }
}
