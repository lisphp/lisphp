<?php

class Lisphp_ProgramTest extends Lisphp_TestCase {
    public $program;
    public $execResult = null;

    function setUp() {
        $this->program = new Lisphp_Program('
            (define add +)
            (define sub (lambda [a b] {- a b}))
            (echo (sub (add 5 7) 3))
        ');
    }

    function testFromFile() {
        $program = Lisphp_Program::load(dirname(__FILE__) . '/sample.lisphp');
        $this->assertEquals(3, count($program));
        try {
            Lisphp_Program::load($f = dirname(__FILE__) . '/sample2.lisphp');
            $this->fail();
        } catch (Lisphp_ParsingException $e) {
            $this->assertEquals(file_get_contents($f), $e->code);
            $this->assertEquals($f, $e->getLisphpFile());
            $this->assertEquals(2, $e->getLisphpLine());
            $this->assertEquals(32, $e->getLisphpColumn());
        }
    }

    function testExecute() {
        $scope = new Lisphp_Scope;
        $scope['define'] = new Lisphp_Runtime_Define;
        $scope['+'] = new Lisphp_Runtime_Arithmetic_Addition;
        $scope['-'] = new Lisphp_Runtime_Arithmetic_Subtraction;
        $scope['lambda'] = new Lisphp_Runtime_Lambda;
        $scope['echo'] = new Lisphp_ProgramTest_Echo($this);
        $this->program->execute($scope);
        $this->assertSame($scope['+'], $scope['add']);
        $this->assertEquals(array(9), $this->execResult);
    }

    function testParse() {
        $this->assertEquals("define", $this->program[0][0]->symbol);
        $this->assertEquals("define", $this->program[1][0]->symbol);
        $this->assertEquals("echo", $this->program[2][0]->symbol);
    }

    function testArrayAccess() {
        $this->assertFalse(isset($this->program[-1]));
        $this->assertTrue(isset($this->program[0]));
        $this->assertTrue(isset($this->program[1]));
        $this->assertTrue(isset($this->program[2]));
        $this->assertFalse(isset($this->program[3]));
        $this->assertType('Lisphp_List', $this->program[0]);
        $this->assertType('Lisphp_List', $this->program[1]);
        $this->assertType('Lisphp_List', $this->program[2]);
        try {
            $this->program[0] = 1;
            $this->fail();
        } catch (BadMethodCallException $e) {
            # pass.
        } catch (Exception $e) {
            $this->fail();
        }
        try {
            unset($this->program[0]);
            $this->fail();
        } catch (BadMethodCallException $e) {
            # pass.
        } catch (Exception $e) {
            $this->fail();
        }
    }

    function testIterator() {
        $i = 0;
        foreach ($this->program as $j => $form) {
            $this->assertType('Lisphp_List', $form);
            $this->assertEquals($i++, $j);
            $forms[] = $form;
        }
        $this->assertEquals(3, count($forms));
    }

    function testCount() {
        $this->assertEquals(3, count($this->program));
    }
}

final class Lisphp_ProgramTest_Echo extends Lisphp_Runtime_Function {
    public $test;

    function __construct(Lisphp_ProgramTest $test) {
        $this->test = $test;
    }

    function execute(array $arguments) {
        $this->test->execResult = $arguments;
    }
}

