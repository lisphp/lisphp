<?php

class Lisphp_SymbolTest extends Lisphp_TestCase
{
    public function testIdentityMap()
    {
        $this->assertSame(Lisphp_Symbol::get('abc'), Lisphp_Symbol::get('abc'));
    }

    public function testNonString()
    {
        $this->setExpectedException('UnexpectedValueException');
        Lisphp_Symbol::get(123);
    }

    public function testInvalidSymbol()
    {
        $this->setExpectedException('UnexpectedValueException');
        Lisphp_Symbol::get('(abc)');
    }

    public function testEvaluate()
    {
        $scope = new Lisphp_Scope;
        $scope['abc'] = 123;
        $symbol = Lisphp_Symbol::get('abc');
        $this->assertEquals(123, $symbol->evaluate($scope));
        $symbol = Lisphp_Symbol::get('def');
        $this->assertNull($symbol->evaluate($scope));
    }

    public function testToString()
    {
        $symbol = Lisphp_Symbol::get('abc');
        $this->assertEquals('abc', $symbol->__toString());
    }
}
