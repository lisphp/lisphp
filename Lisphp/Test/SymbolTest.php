<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Scope.php';

class Lisphp_Test_SymbolTest extends PHPUnit_Framework_TestCase {
    function testUnexpectedValue() {
        $this->setExpectedException('UnexpectedValueException');
        new Lisphp_Symbol(123);
    }

    function testEvaluate() {
        $scope = new Lisphp_Scope;
        $scope['abc'] = 123;
        $symbol = new Lisphp_Symbol('abc');
        $this->assertEquals(123, $symbol->evaluate($scope));
        $symbol = new Lisphp_Symbol('def');
        $this->assertNull($symbol->evaluate($scope));
    }

    function testToString() {
        $symbol = new Lisphp_Symbol('abc');
        $this->assertEquals('abc', $symbol->__toString());
    }
}

