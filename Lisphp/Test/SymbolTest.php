<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Scope.php';

class Lisphp_Test_SymbolTest extends PHPUnit_Framework_TestCase {
    function testIdentityMap() {
        $this->assertSame(Lisphp_Symbol::get('abc'), Lisphp_Symbol::get('abc'));
    }

    function testUnexpectedValue() {
        $this->setExpectedException('UnexpectedValueException');
        Lisphp_Symbol::get(123);
    }

    function testEvaluate() {
        $scope = new Lisphp_Scope;
        $scope['abc'] = 123;
        $symbol = Lisphp_Symbol::get('abc');
        $this->assertEquals(123, $symbol->evaluate($scope));
        $symbol = Lisphp_Symbol::get('def');
        $this->assertNull($symbol->evaluate($scope));
    }

    function testToString() {
        $symbol = Lisphp_Symbol::get('abc');
        $this->assertEquals('abc', $symbol->__toString());
    }
}

