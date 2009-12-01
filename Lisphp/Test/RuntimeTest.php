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
        $form = Lisphp_Parser::parseForm(':(+ 1 2 [- 4 3])', $_);
        $scope = Lisphp_Environment::sandbox();
        $args = new Lisphp_List(array($form));
        $this->assertEquals(4, $eval->apply($scope, $args));
        $args = new Lisphp_List(array($form, Lisphp_Symbol::get('scope')));
        $names = new Lisphp_Scope;
        $names['scope'] = $scope;
        $this->assertEquals(4, $eval->apply($names, $args));
    }

    function testDefine() {
        $define = new Lisphp_Runtime_Define;
        $scope = new Lisphp_Scope(Lisphp_Environment::sandbox());
        $result = $define->apply($scope, new Lisphp_List(array(
            Lisphp_Symbol::get('*pi*'),
            new Lisphp_Literal(pi())
        )));
        $this->assertEquals(pi(), $result);
        $this->assertEquals(pi(), $scope['*pi*']);
        $result = $define->apply($scope, new Lisphp_List(array(
            Lisphp_Symbol::get('pi2'),
            Lisphp_Symbol::get('*pi*')
        )));
        $this->assertEquals(pi(), $result);
        $this->assertEquals(pi(), $scope['pi2']);
        $result = $define->apply($scope, Lisphp_Parser::parseForm('(
            [add a b]
            {+ a b}
        )', $_));
        $this->assertSame($result, $scope['add']);
        $this->assertType('Lisphp_Runtime_Function', $result);
        $this->assertFunction(3, $result, 1, 2);
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
        $this->assertEquals(Lisphp_Symbol::get('abc'),
                            $quote->apply(new Lisphp_Scope, new Lisphp_List(
                                array(Lisphp_Symbol::get('abc'))
                            )));
    }

    function logTest($log) {
        $this->logged = $log;
    }

    function testUserMacro() {
        $scope = Lisphp_Environment::sandbox();
        $scope['log'] = new Lisphp_Runtime_PHPFunction(array($this, 'logTest'));
        $body = Lisphp_Parser::parseForm('{
            (log "testUserMacro")
            (list #scope #arguments)
        }', $_);
        $macro = new Lisphp_Runtime_UserMacro($scope, $body);
        $this->assertSame($scope, $macro->scope);
        $this->assertEquals($body, $macro->body);
        $context = new Lisphp_Scope;
        $args = Lisphp_Parser::parseForm('{a (+ a b)}', $_);
        $retval = $macro->apply($context, $args);
        $this->assertType('Lisphp_List', $retval);
        $this->assertSame($context, $retval[0]);
        $this->assertEquals($args, $retval[1]);
    }

    function testMacro() {
        $macro = new Lisphp_Runtime_Macro;
        $args = Lisphp_Parser::parseForm('{(+ 1 2)}', $_);
        $scope = new Lisphp_Scope;
        $retval = $macro->apply($scope, $args);
        $this->assertType('Lisphp_Runtime_UserMacro', $retval);
        $this->assertSame($scope, $retval->scope);
        $this->assertEquals($args, $retval->body);
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
            Lisphp_Symbol::get('condition'),
            new Lisphp_List(array(Lisphp_Symbol::get('define'),
                                  Lisphp_Symbol::get('a'),
                                  new Lisphp_Literal(1))),
            new Lisphp_List(array(Lisphp_Symbol::get('define'),
                                  Lisphp_Symbol::get('b'),
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

    function applyFunction(Lisphp_Applicable $function) {
        $args = func_get_args();
        array_shift($args);
        $scope = new Lisphp_Scope;
        $symbol = 0;
        foreach ($args as &$value) {
            if ($value instanceof ArrayObject || is_array($value)) {
                $value = new Lisphp_Quote(new Lisphp_List($value));
            } else if (is_object($value) || is_bool($value) || is_null($value)){
                $scope["tmp-$symbol"] = $value;
                $value = Lisphp_Symbol::get('tmp-' . $symbol++);
            } else {
                $value = new Lisphp_Literal($value);
            }
        }
        return $function->apply($scope, new Lisphp_List($args));
    }

    function assertFunction($expected, Lisphp_Applicable $function) {
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
        try {
            $this->applyFunction($func, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        $body = Lisphp_Parser::parseForm('{#arguments}', $_);
        $func = new Lisphp_Runtime_Function($global, new Lisphp_List, $body);
        $this->assertFunction(new Lisphp_List, $func);
        $this->assertFunction(new Lisphp_List(range(1, 3)), $func, 1, 2, 3);
    }

    function testGenericCall() {
        $val = Lisphp_Runtime_Function::call(
            new Lisphp_Runtime_Arithmetic_Addition,
            array(1, 2)
        );
        $this->assertEquals(3, $val);
        try {
            Lisphp_Runtime_Function::call('trim', array('a'));
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass
        }
        try {
            Lisphp_Runtime_Function::call(1, array());
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass
        }
    }

    function testGenericCall530() {
        if (version_compare(phpversion(), '5.3.0', '<')) {
            $this->markTestSkipped('PHP version is less than 5.3.0.');
        }
        eval('$f = function($a, $b) { return $a + $b; };');
        $val = Lisphp_Runtime_Function::call($f, array(1, 2));
        $this->assertEquals(3, $val);
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
        $env = Lisphp_Environment::sandbox();
        $scope = new Lisphp_Scope($env);
        $scope['a'] = 1;
        $retval = $and->apply($scope, Lisphp_Parser::parseForm('{
            (define a 2)
            (define b 0)
            (define a 3)
        }', $_));
        $this->assertEquals(0, $retval);
        $this->assertEquals(2, $scope['a']);
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
        $env = Lisphp_Environment::sandbox();
        $scope = new Lisphp_Scope($env);
        $scope['a'] = 1;
        $retval = $or->apply($scope, Lisphp_Parser::parseForm('{
            (define b 0)
            (define a 2)
            (define a 3)
        }', $_));
        $this->assertEquals(2, $retval);
        $this->assertEquals(2, $scope['a']);
        $this->assertEquals(0, $scope['b']);
    }

    function testEq() {
        $eq = new Lisphp_Runtime_Predicate_Eq;
        $this->assertFunction(false, $eq, new stdClass, new stdClass);
        $o = new stdClass;
        $this->assertFunction(true, $eq, $o, $o);
        $this->assertFunction(true, $eq, 3, 3);
        $this->assertFunction(false, $eq, 3, 3.0);
        $this->assertFunction(true, $eq, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'bar');
        $this->assertFunction(true, $eq, 3.0, 3.0, 3.0);
        $this->assertFunction(false, $eq, 3, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($eq);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($eq, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testEqual() {
        $eq = new Lisphp_Runtime_Predicate_Equal;
        $this->assertFunction(true, $eq, new stdClass, new stdClass);
        $this->assertFunction(true, $eq, 3, 3);
        $this->assertFunction(true, $eq, 3, 3.0);
        $this->assertFunction(true, $eq, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'bar');
        $this->assertFunction(true, $eq, 3.0, 3.0, 3.0);
        $this->assertFunction(true, $eq, 3, 3.0, 3.0);
        $this->assertFunction(true, $eq, 'foo', 'foo', 'foo');
        $this->assertFunction(false, $eq, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($eq);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($eq, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testNotEq() {
        $ne = new Lisphp_Runtime_Predicate_NotEq;
        $this->assertFunction(true, $ne, new stdClass, new stdClass);
        $o = new stdClass;
        $this->assertFunction(false, $ne, $o, $o);
        $this->assertFunction(false, $ne, 3, 3);
        $this->assertFunction(true, $ne, 3, 3.0);
        $this->assertFunction(false, $ne, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'bar');
        $this->assertFunction(false, $ne, 3.0, 3.0, 3.0);
        $this->assertFunction(true, $ne, 3, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($ne);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($ne, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testNotEqual() {
        $ne = new Lisphp_Runtime_Predicate_NotEqual;
        $this->assertFunction(false, $ne, new stdClass, new stdClass);
        $o = new stdClass;
        $this->assertFunction(false, $ne, $o, $o);
        $this->assertFunction(false, $ne, 3, 3);
        $this->assertFunction(false, $ne, 3, 3.0);
        $this->assertFunction(false, $ne, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'bar');
        $this->assertFunction(false, $ne, 3.0, 3.0, 3.0);
        $this->assertFunction(false, $ne, 3, 3.0, 3.0);
        $this->assertFunction(false, $ne, 'foo', 'foo', 'foo');
        $this->assertFunction(true, $ne, 'foo', 'foo', 'bar');
        try {
            $this->applyFunction($ne);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($ne, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testTypePredicate() {
        $int = new Lisphp_Runtime_Predicate_Type('int');
        $this->assertEquals('int', $int->type);
        $this->assertFunction(true, $int, 123);
        $this->assertFunction(false, $int, 3.14);
        $this->assertFunction(false, $int, 'abc');
        $string = new Lisphp_Runtime_Predicate_Type('string');
        $this->assertEquals('string', $string->type);
        $this->assertFunction(true, $string, 'hello');
        $this->assertFunction(false, $string, 123);
        $env = Lisphp_Runtime_Predicate_Type::getFunctions();
        foreach (Lisphp_Runtime_Predicate_Type::$types as $type) {
            $this->assertType('Lisphp_Runtime_Predicate_Type', $env["$type?"]);
            $this->assertEquals($type, $env["$type?"]->type);
        }
        $this->assertFunction(true, $env['nil?'], null);
        $this->assertFunction(false, $env['nil?'], 123);
    }

    function testIsA() {
        $isa = new Lisphp_Runtime_Predicate_IsA;
        $this->assertFunction(true, $isa,
                              new stdClass,
                              new Lisphp_Runtime_PHPClass('stdClass'));
        $this->assertFunction(false, $isa,
                              new stdClass,
                              new Lisphp_Runtime_PHPClass('ArrayObject'));
        $this->assertFunction(false, $isa,
                              1, new Lisphp_Runtime_PHPClass('stdClass'));
        $this->assertFunction(true, $isa,
                              new stdClass,
                              new Lisphp_Runtime_PHPClass('ArrayObject'),
                              new Lisphp_Runtime_PHPClass('stdClass'));
        $this->assertFunction(false, $isa,
                              new stdClass,
                              new Lisphp_Runtime_PHPClass('ArrayObject'),
                              new Lisphp_Runtime_PHPClass('ArrayIterator'));
        try {
            $this->applyFunction($isa);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($isa, 1);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testList() {
        $list = new Lisphp_Runtime_List;
        $this->assertFunction(new Lisphp_List, $list);
        $this->assertFunction(new Lisphp_List(array(1, 2, 3, 4)),
                              $list, 1, 2, 3, 4);
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

    function testAt() {
        $at = new Lisphp_Runtime_List_At;
        $this->assertFunction(1, $at, new Lisphp_List(array(1, 2, 3)), 0);
        $this->assertFunction(3, $at, new Lisphp_List(array(1, 2, 3)), 2);
        try {
            $this->applyFunction($at, new Lisphp_List(array(1, 2, 3)), 3);
            $this->fail();
        } catch (OutOfRangeException $e) {
            # pass.
        }
    }

    function testCount() {
        $count = new Lisphp_Runtime_List_Count;
        $this->assertFunction(0, $count, array());
        $this->assertFunction(3, $count, array(1, 2, 3));
        $this->assertFunction(0, $count, new Lisphp_List);
        $this->assertFunction(3, $count, new Lisphp_List(array(1, 2, 3)));
        $this->assertFunction(0, $count, '');
        $this->assertFunction(3, $count, 'abc');
    }

    function testArray() {
        $array = new Lisphp_Runtime_Array;
        $this->assertFunction(array(), $array);
        $this->assertFunction(array(1, 2, 3), $array, 1, 2, 3);
    }

    function testDict() {
        $dict = new Lisphp_Runtime_Dict;
        $scope = new Lisphp_Scope;
        $scope['a'] = 'key';
        $retval = $dict->apply($scope, Lisphp_Parser::parseForm('{
            (a 1) ("key2" 2) (3) 4
        }', $_));
        $this->assertEquals(array('key' => 1, 'key2' => 2, 3, 4), $retval);
    }

    function testMap() {
        $map = new Lisphp_Runtime_List_Map;
        $func = new Lisphp_Runtime_Function(
            Lisphp_Environment::sandbox(),
            Lisphp_Parser::parseForm('[a]', $_),
            Lisphp_Parser::parseForm('{(+ a 1)}', $_)
        );
        $this->assertFunction(new Lisphp_List, $map, $func, array());
        $this->assertFunction(new Lisphp_List(array(2, 3)),
                              $map, $func, array(1, 2));
        $func = new Lisphp_Runtime_Function(new Lisphp_Scope,
                                            Lisphp_Parser::parseForm('[]', $_),
                                            Lisphp_Parser::parseForm('{
                                                #arguments
                                            }', $_));
        $this->assertFunction(
            new Lisphp_List(array(new Lisphp_List(array(1, 4)),
                                  new Lisphp_List(array(2, 5)),
                                  new Lisphp_List(array(3, 6)))),
            $map, $func, array(1, 2, 3), array(4, 5, 6)
        );
        try {
            $this->applyFunction($map);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($map, $func);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
        try {
            $this->applyFunction($map, 1, array(1));
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testFilter() {
        $filter = new Lisphp_Runtime_List_Filter;
        $this->assertFunction(new Lisphp_List(array(1, 3, 5)),
                              $filter,
                              new Lisphp_Runtime_Predicate_Type('int'),
                              array(1, '2', 3, array(4), 5));
    }

    function testFoldl() {
        $fold = new Lisphp_Runtime_List_Fold;
        $agg = new Lisphp_Runtime_Arithmetic_Subtraction;
        $this->assertFunction(3, $fold, $agg, array(25, 9, 5, 7, 1));
        $this->assertFunction(3, $fold, $agg, array(9, 5, 7, 1), 25);
        $this->assertFunction(25, $fold, $agg, array(), 25);
        try {
            $this->applyFunction($fold, $agg, array());
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testConcat() {
        $concat = new Lisphp_Runtime_String_Concat;
        $this->assertFunction('ab', $concat, 'a', 'b');
        $this->assertFunction('hello world!', $concat, 'hello', ' world', '!');
        try {
            $this->applyFunction($concat);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            # pass.
        }
    }

    function testStringJoin() {
        $join = new Lisphp_Runtime_String_StringJoin;
        $this->assertFunction('one two three', $join,
                              array('one', 'two', 'three'), ' ');
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
        $this->assertTrue($class->isClassOf(new Lisphp_Test_SampleClass));
        $this->assertFalse($class->isClassOf(new stdClass));
        $this->assertFalse($class->isClassOf(213));
    }

    function testUse() {
        $use = new Lisphp_Runtime_Use;
        $env = Lisphp_Environment::sandbox();
        $scope = new Lisphp_Scope($env);
        $values = $use->apply($scope,
                              Lisphp_Parser::parseForm('{
                                  array_merge
                                  array-slice
                                  [implode array->string]
                                  <ArrayObject>
                                  <Lisphp_Symbol>
                                  Lisphp/<Program>
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
        $this->assertEquals('implode', $values[2]->callback);
        $this->assertSame($values[2], $scope['array->string']);
        $this->assertNull($env['implode']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[3]);
        $this->assertEquals('ArrayObject', $values[3]->class->getName());
        $this->assertSame($values[3], $scope['<ArrayObject>']);
        $this->assertNull($env['<ArrayObject>']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[4]);
        $this->assertEquals('Lisphp_Symbol', $values[4]->class->getName());
        $this->assertSame($values[4], $scope['<Lisphp_Symbol>']);
        $this->assertNull($env['<Lisphp_Symbol>']);
        $this->assertType('Lisphp_Runtime_PHPClass', $values[5]);
        $this->assertEquals('Lisphp_Program', $values[5]->class->getName());
        $this->assertSame($values[5], $scope['Lisphp/<Program>']);
        $this->assertType('Lisphp_Runtime_PHPFunction',
                          $scope['Lisphp/<Program>/load']);
        $this->assertEquals(array('Lisphp_Program', 'load'),
                            $scope['Lisphp/<Program>/load']->callback);
        $this->assertNull($env['Lisphp/<Program>']);
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

