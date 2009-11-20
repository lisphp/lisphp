<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Runtime.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Environment.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Literal.php';
require_once 'Lisphp/Parser.php';

final class Lisphp_Test_SampleClass {
    const PI = 3.14;

    static function a() {
        return 'a';
    }

    static function b() {
        return 'b';
    }
}

class Lisphp_Test_RuntimeTest extends PHPUnit_Framework_TestCase {
    function testEval() {
        $eval = new Lisphp_Runtime_Eval;
        $form = Lisphp_Parser::parseForm('(+ 1 2 [- 4 3])', $_);
        $scope = Lisphp_Environment::sandbox();
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

    function testLet() {
        $let = new Lisphp_Runtime_Let;
        $scope = Lisphp_Environment::sandbox();
        $scope['a'] = 1;
        $scope['c'] = 1;
        $retval = $let->apply(
            $scope,
            Lisphp_Parser::parseForm('{[(a 2) (b 1)] (define c 2) (+ a b)}', $_)
        );
        $this->assertEquals(2, $scope['c']);
        $this->assertEquals(3, $retval);
        $this->assertEquals(1, $scope['a']);
        $this->assertNull($scope['b']);
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
        $args = Lisphp_Parser::parseForm('{[a b] (define x 2) (+ a b)}', $_);
        $func = $lambda->apply($scope, $args);
        $this->assertType('Lisphp_Runtime_Function', $func);
        $this->assertSame($scope, $func->scope);
        $this->assertEquals($args->car(), $func->parameters);
        $this->assertEquals($args->cdr(), $func->body);
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
        $global = new Lisphp_Scope(Lisphp_Environment::sandbox());
        $global['x'] = 1;
        $params = Lisphp_Parser::parseForm('[a b]', $_);
        $body = Lisphp_Parser::parseForm('{(define x 2) (+ a b)}', $_);
        $func = new Lisphp_Runtime_Function($global, $params, $body);
        $this->assertSame($global, $func->scope);
        $this->assertEquals($params, $func->parameters);
        $this->assertEquals($body, $func->body);
        $this->assertFunction(3, $func, 1, 2);
        $this->assertEquals(2, $global['x']);
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
        try {
            new Lisphp_Runtime_PHPFunction('undefined_function_name');
            $this->fail();
        } catch (UnexpectedValueException $e) {
            # pass
        }
    }

    function testPHPClass() {
        $class = new Lisphp_Runtime_PHPClass('ArrayObject');
        $obj = $this->applyFunction($class, array(1, 2, 3));
        $this->assertType('ArrayObject', $obj);
        $this->assertEquals(array(1, 2, 3), $obj->getArrayCopy());
        try {
            new Lisphp_Runtime_PHPClass('UndefinedClassName');
            $this->fail();
        } catch (UnexpectedValueException $e) {
            # pass
        }
        $class = new Lisphp_Runtime_PHPClass('Lisphp_Test_SampleClass');
        $methods = $class->getStaticMethods();
        $this->assertEquals(2, count($methods));
        $this->assertType('Lisphp_Runtime_PHPFunction', $methods['a']);
        $this->assertEquals(array('Lisphp_Test_SampleClass', 'a'),
                            $methods['a']->callback);
        $this->assertType('Lisphp_Runtime_PHPFunction', $methods['b']);
        $this->assertEquals(array('Lisphp_Test_SampleClass', 'b'),
                            $methods['b']->callback);
    }

    function testUse() {
        $use = new Lisphp_Runtime_Use;
        $env = Lisphp_Environment::sandbox();
        $scope = new Lisphp_Scope($env);
        $values = $use->apply($scope,
                              Lisphp_Parser::parseForm('{
                                  array_merge
                                  array-slice
                                  [substr substring]
                                  <ArrayObject>
                                  <Lisphp_Symbol>
                                  <Lisphp/List>
                                  [<Lisphp-Scope> scope]
                              }', $_));
        $this->assertType('Lisphp_Runtime_PHPFunction', $values[0]);
        $this->assertEquals('array_merge', $values[0]->callback);
        $this->assertSame($values[0], $scope['array_merge']);
        $this->assertNull($env['array_merge']);
        $this->assertType('Lisphp_Runtime_PHPFunction', $values[1]);
        $this->assertEquals('array_slice', $values[1]->callback);
        $this->assertSame($values[1], $scope['array-slice']);
        $this->assertNull($env['array-slice']);
        $this->assertType('Lisphp_Runtime_PHPFunction', $values[2]);
        $this->assertEquals('substr', $values[2]->callback);
        $this->assertSame($values[2], $scope['substring']);
        $this->assertNull($env['substring']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[3]);
        $this->assertEquals('ArrayObject', $values[3]->class->getName());
        $this->assertSame($values[3], $scope['<ArrayObject>']);
        $this->assertNull($env['<ArrayObject>']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[4]);
        $this->assertEquals('Lisphp_Symbol', $values[4]->class->getName());
        $this->assertSame($values[4], $scope['<Lisphp_Symbol>']);
        $this->assertNull($env['<Lisphp_Symbol>']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[5]);
        $this->assertEquals('Lisphp_List', $values[5]->class->getName());
        $this->assertSame($values[5], $scope['<Lisphp/List>']);
        $this->assertNull($env['<Lisphp/List>']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[6]);
        $this->assertEquals('Lisphp_Scope', $values[6]->class->getName());
        $this->assertSame($values[6], $scope['scope']);
        $this->assertNull($env['scope']);
        try {
            $use->apply(
                $scope,
                Lisphp_Parser::parseForm('(undefined-function-name)', $_)
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass
        }
        try {
            $use->apply(
                $scope,
                Lisphp_Parser::parseForm('(<UndefinedClassName>)', $_)
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass
        }
    }
}

