<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Literal.php';
require_once 'Lisphp/Runtime/Define.php';

class Lisphp_Test_ListTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->list = new Lisphp_List(array(
            new Lisphp_Symbol('define'),
            new Lisphp_Symbol('pi'),
            new Lisphp_Literal(3.14)
        ));
    }

    function testInvalidApplication() {
        $this->setExpectedException('InvalidApplicationException');
        $this->list->evaluate(new Lisphp_Scope);
    }

    function testEvaluate() {
        $scope = new Lisphp_Scope;
        $scope['define'] = new Lisphp_Runtime_Define;
        $this->assertEquals(3.14, $this->list->evaluate($scope));
        $this->assertEquals(3.14, $scope['pi']);
    }

    function testCar() {
        $this->assertSame($this->list[0], $this->list->car());
    }

    function testCdr() {
        $this->assertEquals(new Lisphp_List(array(new Lisphp_Symbol('pi'),
                                                  new Lisphp_Literal(3.14))),
                            $this->list->cdr());
    }

    function testToString() {
        $this->assertEquals('(define pi 3.14)', $this->list->__toString());
    }
}

