<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Runtime.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Literal.php';

class Lisphp_Test_RuntimeTest extends PHPUnit_Framework_TestCase {
    function testDefine() {
        $define = new Lisphp_Runtime_Define;
        $scope = new Lisphp_Scope;
        $result = $define->apply($scope, new Lisphp_List(array(
            new Lisphp_Symbol('*pi*'),
            new Lisphp_Literal(PI)
        )));
        $this->assertEquals(PI, $result);
        $this->assertEquals(PI, $scope['*pi*']);
        $result = $define->apply($scope, new Lisphp_List(array(
            new Lisphp_Symbol('pi2'),
            new Lisphp_Symbol('*pi*')
        )));
        $this->assertEquals(PI, $result);
        $this->assertEquals(PI, $scope['pi2']);
    }

    function testLambda() {
        $lambda = new Lisphp_Runtime_Lambda;
        $scope = new Lisphp_Scope;
        $params = new Lisphp_List(array(new Lisphp_Symbol('a'),
                                        new Lisphp_Symbol('b')));
        $body = new Lisphp_List(array(new Lisphp_Symbol('+'),
                                      new Lisphp_Symbol('a'),
                                      new Lisphp_Symbol('b')));
        $func = $lambda->apply($scope, new Lisphp_List(array($params, $body)));
        $this->assertType('Lisphp_Runtime_Function', $func);
        $this->assertSame($scope, $func->scope);
        $this->assertEquals($params, $func->parameters);
        $this->assertEquals($body, $func->body);
    }

    function assertFunction($expected, Lisphp_Runtime_Function $function) {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        foreach ($args as &$value) {
            $value = new Lisphp_Literal($value);
        }
        $retval = $function->apply(new Lisphp_Scope, new Lisphp_List($args));
        $this->assertEquals($expected, $retval);
    }

    function testFunction() {
        $global = new Lisphp_Scope;
        $params = new Lisphp_List(array());
        $body = new Lisphp_Literal('test');
        $func = new Lisphp_Runtime_Function($global, $params, $body);
        $this->assertSame($global, $func->scope);
        $this->assertEquals($params, $func->parameters);
        $this->assertEquals($body, $func->body);
        $this->assertFunction('test', $func);
    }

    function testAdd() {
        $add = new Lisphp_Runtime_Arithmetic_Addition;
        $this->assertFunction(5, $add, 5);
        $this->assertFunction(10, $add, 5, 5);
        $this->assertFunction(6, $add, 1, 2, 3);
    }

    function testSubtract() {
        $sub = new Lisphp_Runtime_Arithmetic_Subtraction;
        $this->assertFunction(-5, $sub, 5);
        $this->assertFunction(2, $sub, 5, 3);
        $this->assertFunction(1, $sub, 5, 3, 1);
    }

    function testMultiply() {
        $mul = new Lisphp_Runtime_Arithmetic_Multiplication;
        $this->assertFunction(1, $mul);
        $this->assertFunction(5, $mul, 5);
        $this->assertFunction(25, $mul, 5, 5);
        $this->assertFunction(50, $mul, 5, 5, 2);
    }

    function testDivide() {
        $div = new Lisphp_Runtime_Arithmetic_Division;
        $this->assertFunction(5, $div, 25, 5);
        $this->assertFunction(5, $div, 50, 2, 5);
    }

    function testMod() {
        $mod = new Lisphp_Runtime_Arithmetic_Modulus;
        $this->assertFunction(0, $mod, 25, 5);
        $this->assertFunction(1, $mod, 25, 4);
    }
}

