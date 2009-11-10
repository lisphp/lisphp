<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Symbol.php';

class Lisphp_Test_ScopeTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->scope = new Lisphp_Scope;
        $this->scope['abc'] = 1;
        $this->scope['def'] = true;
        $this->scope[new Lisphp_Symbol('ghi')] = null;
    }

    function testGet() {
        $this->assertEquals(1, $this->scope['abc']);
        $this->assertEquals(true, $this->scope[new Lisphp_Symbol('def')]);
        $this->assertNull($this->scope['ghi']);
        $this->assertNull($this->scope['x']);
    }

    function testExists() {
        $this->assertTrue(isset($this->scope['abc']));
        $this->assertTrue(isset($this->scope['x']));
    }

    function testUnset() {
        unset($this->scope['abc']);
        $this->assertNull($this->scope['abc']);
    }

    function testSuperscope() {
        $scope = new Lisphp_Scope($this->scope);
        $this->assertSame($this->scope, $scope->superscope);
        $this->assertEquals(1, $scope['abc']);
        $this->assertNull($scope['x']);
        $this->scope['abc'] = 2;
        $this->assertEquals(2, $this->scope['abc']);
        $this->assertEquals(2, $scope['abc']);
        $scope['abc'] = 3;
        $this->assertEquals(3, $this->scope['abc']);
        $this->assertEquals(3, $scope['abc']);
        $scope['abc'] = null;
        $this->assertNull($scope['abc']);
        $this->assertNull($this->scope['abc']);
        $scope['def'] = false;
        unset($scope['def']);
        $this->assertNull($scope['def']);
        $this->assertNull($this->scope['def']);
    }

    function testLet() {
        $scope = new Lisphp_Scope($this->scope);
        $scope->let('abc', 'overridden');
        $this->assertEquals('overridden', $scope['abc']);
    }
}

