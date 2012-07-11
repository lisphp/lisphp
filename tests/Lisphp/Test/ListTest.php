<?php

class Lisphp_Test_ListTest extends Lisphp_Test_TestCase {
    function setUp() {
        $this->list = new Lisphp_List(array(
            Lisphp_Symbol::get('define'),
            Lisphp_Symbol::get('pi'),
            new Lisphp_Literal(3.14)
        ));
    }

    function testInvalidApplication() {
        $this->setExpectedException('InvalidApplicationException');
        $this->list->evaluate(new Lisphp_Scope);
    }

    function testInvalidApplication2() {
        $this->setExpectedException('InvalidApplicationException');
        $l = Lisphp_Parser::parseForm('("trim" "  hello  ")', $_);
        $l->evaluate(new Lisphp_Scope);
    }

    function testEvaluate() {
        $scope = new Lisphp_Scope;
        $scope['define'] = new Lisphp_Runtime_Define;
        $this->assertEquals(3.14, $this->list->evaluate($scope));
        $this->assertEquals(3.14, $scope['pi']);
    }

    function testEvaluate530() {
        if (version_compare(phpversion(), '5.3.0', '<')) {
            $this->markTestSkipped('PHP version is less than 5.3.0.');
        }
        $scope = new Lisphp_Scope;
        eval('$scope["f"] = function($a, $b) { return $a + $b; };');
        $list = new Lisphp_List(array(
            Lisphp_Symbol::get('f'),
            new Lisphp_Literal(123),
            new Lisphp_Literal(456)
        ));
        $this->assertEquals(579, $list->evaluate($scope));
    }

    function testCar() {
        $this->assertSame($this->list[0], $this->list->car());
    }

    function testCdr() {
        $this->assertEquals(new Lisphp_List(array(Lisphp_Symbol::get('pi'),
                                                  new Lisphp_Literal(3.14))),
                            $this->list->cdr());
    }

    function testToString() {
        $this->assertEquals('(define pi 3.14)', $this->list->__toString());
    }
}

