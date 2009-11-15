<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Program.php';
require_once 'Lisphp/Form.php';
require_once 'Lisphp/Runtime.php';

class Lisphp_Test_ProgramTest extends PHPUnit_Framework_TestCase {
    public $program;
    public $execResult = null;

    function setUp() {
        $this->program = new Lisphp_Program('
            (define add +)
            (define sub (lambda [a b] {- a b}))
            (echo (sub (add 5 7) 3))
        ');
    }

    function testExecute() {
        $scope = new Lisphp_Scope;
        $scope['define'] = new Lisphp_Runtime_Define;
        echo 'TODO: testExecute!!!!!!!!!!!!!!!!!!!!!!!!';
        return;
        $this->program->execute($scope);
        $this->assertSame($scope['+'], $scope['add']);
        $this->assertEquals(9, $this->execResult);
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

