<?php

class Lisphp_Test_TestCase extends PHPUnit_Framework_TestCase {
    function assertType($class, $instance) {
        $this->assertTrue($instance instanceof $class);
    }
}

