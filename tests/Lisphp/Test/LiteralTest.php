<?php

class Lisphp_Test_LiteralTest extends Lisphp_Test_TestCase {
    static $values = array('integer' => 123, 'real' => 3.14, 'string' => 'abc');

    function testUnexpectedValue() {
        $this->setExpectedException('UnexpectedValueException');
        new Lisphp_Literal(new stdClass);
    }

    function testValue() {
        foreach (self::$values as $_ => $value) {
            $literal = new Lisphp_Literal($value);
            $this->assertEquals($value, $literal->value);
        }
    }

    function testEvaluate() {
        foreach (self::$values as $_ => $value) {
            $literal = new Lisphp_Literal($value);
            $this->assertEquals($value, $literal->evaluate(new Lisphp_Scope));
        }
    }

    function testPredicate() {
        foreach (self::$values as $type => $value) {
            $literal = new Lisphp_Literal($value);
            $this->assertTrue($literal->{"is$type"}());
        }
    }
}

