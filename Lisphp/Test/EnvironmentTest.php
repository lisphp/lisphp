<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Environment.php';
require_once 'Lisphp/Runtime.php';

class Lisphp_Test_EnvironmentTest extends PHPUnit_Framework_TestCase {
    function testSandbox() {
        $scope = Lisphp_Environment::sandbox();
        $this->assertNull($scope['nil']);
        $this->assertTrue($scope['true']);
        $this->assertFalse($scope['false']);
        $this->assertTrue($scope['#t']);
        $this->assertFalse($scope['#f']);
        $this->assertType('Lisphp_Runtime_Eval', $scope['eval']);
        $this->assertType('Lisphp_Runtime_Quote', $scope['quote']);
        $this->assertType('Lisphp_Runtime_Define', $scope['define']);
        $this->assertType('Lisphp_Runtime_Let', $scope['let']);
        $this->assertType('Lisphp_Runtime_Lambda', $scope['lambda']);
        $this->assertType('Lisphp_Runtime_Apply', $scope['apply']);
        $this->assertType('Lisphp_Runtime_List_Car', $scope['car']);
        $this->assertType('Lisphp_Runtime_List_Cdr', $scope['cdr']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Addition', $scope['+']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Subtraction', $scope['-']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Multiplication',
                          $scope['*']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Division', $scope['/']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Modulus', $scope['%']);
        $this->assertType('Lisphp_Runtime_Logical_Not', $scope['not']);
        $this->assertType('Lisphp_Runtime_Logical_And', $scope['and']);
        $this->assertType('Lisphp_Runtime_Logical_Or', $scope['or']);
        $this->assertType('Lisphp_Runtime_Logical_If', $scope['if']);
    }
}

