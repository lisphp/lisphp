<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Runtime.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Literal.php';
require_once 'Lisphp/Parser.php';

class Lisphp_Test_RuntimeTest extends PHPUnit_Framework_TestCase {
    function testEval() {
        $eval = new Lisphp_Runtime_Eval;
        $form = Lisphp_Parser::parseForm('(+ 1 2 [- 4 3])', $_);
        $scope = new Lisphp_Scope;
        $scope['+'] = new Lisphp_Runtime_Arithmetic_Addition;
        $scope['-'] = new Lisphp_Runtime_Arithmetic_Subtraction;
        $args = new Lisphp_List(array($form));
        $this->assertEquals(4, $eval->apply($scope, $args));
        $args = new Lisphp_List(array($form, $scope));
        $this->assertEquals(4, $eval->apply(new Lisphp_Scope, $args));
    }

    function testDefine() {
        $define = new Lisphp_Runtime_Define;
        $scope = new Lisphp_Scope;
        $result = $define->apply($scope, new Lisphp_List(array(
            new Lisphp_Symbol('*pi*'),
            new Lisphp_Literal(pi())
        )));
        $this->assertEquals(pi(), $result);
        $this->assertEquals(pi(), $scope['*pi*']);
        $result = $define->apply($scope, new Lisphp_List(array(
            new Lisphp_Symbol('pi2'),
            new Lisphp_Symbol('*pi*')
        )));
        $this->assertEquals(pi(), $result);
        $this->assertEquals(pi(), $scope['pi2']);
    }

    function testQuote() {
        $quote = new Lisphp_Runtime_Quote;
        $this->assertEquals(new Lisphp_Symbol('abc'),
                            $quote->apply(new Lisphp_Scope, new Lisphp_List(
                                array(new Lisphp_Symbol('abc'))
                            )));
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

    function testIf() {
        $if = new Lisphp_Runtime_Logical_If;
        $scope = new Lisphp_Scope;
        $scope['define'] = new Lisphp_Runtime_Define;
        $args = array(
            new Lisphp_Symbol('condition'),
            new Lisphp_List(array(new Lisphp_Symbol('define'),
                                  new Lisphp_Symbol('a'),
                                  new Lisphp_Literal(1))),
            new Lisphp_List(array(new Lisphp_Symbol('define'),
                                  new Lisphp_Symbol('b'),
                                  new Lisphp_Literal(2)))
        );
        $scope['condition'] = true;
        $scope['a'] = $scope['b'] = 0;
        $retval = $if->apply($scope, new Lisphp_List($args));
        $this->assertEquals(1, $retval);
        $this->assertEquals(1, $scope['a']);
        $this->assertEquals(0, $scope['b']);
        $scope['condition'] = false;
        $scope['a'] = $scope['b'] = 0;
        $retval = $if->apply($scope, new Lisphp_List($args));
        $this->assertEquals(2, $retval);
        $this->assertEquals(0, $scope['a']);
        $this->assertEquals(2, $scope['b']);
    }

    function applyFunction(Lisphp_Runtime_Function $function) {
        $args = func_get_args();
        array_shift($args);
        $scope = new Lisphp_Scope;
        $symbol = 0;
        foreach ($args as &$value) {
            if ($value instanceof ArrayObject || is_array($value)) {
                $value = new Lisphp_Quote(new Lisphp_List($value));
            } else if (is_object($value) || is_bool($value) || is_null($value)){
                $scope["tmp-$symbol"] = $value;
                $value = new Lisphp_Symbol('tmp-' . $symbol++);
            } else {
                $value = new Lisphp_Literal($value);
            }
        }
        return $function->apply($scope, new Lisphp_List($args));
    }

    function assertFunction($expected, Lisphp_Runtime_Function $function) {
        $args = func_get_args();
        array_shift($args);
        $this->assertEquals(
            $expected,
            call_user_func_array(array($this, 'applyFunction'), $args)
        );
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

    function testApply() {
        $apply = new Lisphp_Runtime_Apply;
        $add = new Lisphp_Runtime_Arithmetic_Addition;
        $this->assertFunction(9, $apply, $add, new Lisphp_List(array(2, 3, 4)));
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

    function testNot() {
        $not = new Lisphp_Runtime_Logical_Not;
        $this->assertFunction(false, $not, true);
        $this->assertFunction(true, $not, false);
        $this->assertFunction(false, $not, 1);
        $this->assertFunction(false, $not, 2);
        $this->assertFunction(true, $not, 0);
        $this->assertFunction(false, $not, 'abc');
        $this->assertFunction(true, $not, '');
    }

    function testAnd() {
        $and = new Lisphp_Runtime_Logical_And;
        $this->assertFunction(false, $and, false);
        $this->assertFunction(true, $and, true);
        $this->assertFunction(false, $and, false, false);
        $this->assertFunction(false, $and, false, true);
        $this->assertFunction(false, $and, true, false);
        $this->assertFunction(true, $and, true, true);
        $this->assertFunction(false, $and, false, false, false);
        $this->assertFunction(false, $and, false, true, false);
        $this->assertFunction(false, $and, false, false, true);
        $this->assertFunction(false, $and, false, true, true);
        $this->assertFunction(true, $and, true, true, true);
        $this->assertFunction('', $and, 'a', '');
        $this->assertFunction(null, $and, 'a', null);
        $this->assertFunction('b', $and, 'a', 'b');
        $this->assertFunction('', $and, 'a', 'b', '');
        $this->assertFunction(null, $and, 'a', 'b', null);
        $this->assertFunction('c', $and, 'a', 'b', 'c');
    }

    function testOr() {
        $or = new Lisphp_Runtime_Logical_Or;
        $this->assertFunction(false, $or, false);
        $this->assertFunction(true, $or, true);
        $this->assertFunction(false, $or, false, false);
        $this->assertFunction(true, $or, true, false);
        $this->assertFunction(true, $or, false, true);
        $this->assertFunction(true, $or, true, true);
        $this->assertFunction(false, $or, false, false, false);
        $this->assertFunction(true, $or, false, false, true);
        $this->assertFunction(true, $or, false, true, false);
        $this->assertFunction(true, $or, true, false, false);
        $this->assertFunction(true, $or, true, true, false);
        $this->assertFunction(true, $or, false, true, true);
        $this->assertFunction(true, $or, true, false, true);
        $this->assertFunction(true, $or, true, true, true);
        $this->assertFunction('a', $or, 'a', '');
        $this->assertFunction('', $or, null, '');
        $this->assertFunction('b', $or, '', 'b');
        $this->assertFunction('a', $or, 'a', 'b', 'c');
        $this->assertFunction('c', $or, false, null, 'c');
    }

    function testCar() {
        $car = new Lisphp_Runtime_List_Car;
        $this->assertFunction(1, $car, array(1, 2, 3));
        try {
            $this->applyFunction($car, new Lisphp_List);
            $this->fails();
        } catch (UnexpectedValueException $e) {
            # pass.
        }
    }

    function testCdr() {
        $cdr = new Lisphp_Runtime_List_Cdr;
        $this->assertFunction(new Lisphp_List(array(2, 3)),
                              $cdr, array(1, 2, 3));
        $this->assertFunction(null, $cdr, array());
        $this->assertFunction(new Lisphp_List, $cdr, array(1));
    }

    function methodTest($a) {
        return array($this, $a);
    }

    function testPHPFunction() {
        $substr = new Lisphp_Runtime_PHPFunction('substr');
        $this->assertFunction('world', $substr, 'hello world', 6);
        $method = new Lisphp_Runtime_PHPFunction(array($this, 'methodTest'));
        $this->assertFunction(array($this, 123), $method, 123);
    }

    function testPHPClass() {
        $class = new Lisphp_Runtime_PHPClass('ArrayObject');
        $obj = $this->applyFunction($class, array(1, 2, 3));
        $this->assertType('ArrayObject', $obj);
        $this->assertEquals(array(1, 2, 3), $obj->getArrayCopy());
    }
}

