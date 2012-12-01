<?php

class Lisphp_LiteralTest extends Lisphp_TestCase
{
    public static $values = array('integer' => 123, 'real' => 3.14, 'string' => 'abc');

    public function testUnexpectedValue()
    {
        $this->setExpectedException('UnexpectedValueException');
        new Lisphp_Literal(new stdClass);
    }

    public function testValue()
    {
        foreach (self::$values as $_ => $value) {
            $literal = new Lisphp_Literal($value);
            $this->assertEquals($value, $literal->value);
        }
    }

    public function testEvaluate()
    {
        foreach (self::$values as $_ => $value) {
            $literal = new Lisphp_Literal($value);
            $this->assertEquals($value, $literal->evaluate(new Lisphp_Scope));
        }
    }

    public function testPredicate()
    {
        foreach (self::$values as $type => $value) {
            $literal = new Lisphp_Literal($value);
            $this->assertTrue($literal->{"is$type"}());
        }
    }
}
