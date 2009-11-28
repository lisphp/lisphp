<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Environment.php';
require_once 'Lisphp/Runtime.php';

class Lisphp_Test_EnvironmentTest extends PHPUnit_Framework_TestCase {
    function testSandbox($scope = null) {
        if (is_null($scope)) {
            $scope = Lisphp_Environment::sandbox();
        }
        $this->assertType('Lisphp_Scope', $scope);
        $this->assertNull($scope['nil']);
        $this->assertTrue($scope['true']);
        $this->assertFalse($scope['false']);
        $this->assertTrue($scope['#t']);
        $this->assertFalse($scope['#f']);
        $this->assertType('Lisphp_Runtime_Eval', $scope['eval']);
        $this->assertType('Lisphp_Runtime_Quote', $scope['quote']);
        $this->assertType('Lisphp_Runtime_PHPFunction', $scope['symbol']);
        $this->assertEquals(array('Lisphp_Symbol', 'get'),
                            $scope['symbol']->callback);
        $this->assertType('Lisphp_Runtime_Define', $scope['define']);
        $this->assertType('Lisphp_Runtime_Let', $scope['let']);
        $this->assertType('Lisphp_Runtime_Macro', $scope['macro']);
        $this->assertType('Lisphp_Runtime_Lambda', $scope['lambda']);
        $this->assertType('Lisphp_Runtime_Apply', $scope['apply']);
        $this->assertType('Lisphp_Runtime_Dict', $scope['dict']);
        $this->assertType('Lisphp_Runtime_Array', $scope['array']);
        $this->assertType('Lisphp_Runtime_List', $scope['list']);
        $this->assertType('Lisphp_Runtime_List_Car', $scope['car']);
        $this->assertType('Lisphp_Runtime_List_Cdr', $scope['cdr']);
        $this->assertType('Lisphp_Runtime_List_At', $scope['at']);
        $this->assertType('Lisphp_Runtime_List_Count', $scope['count']);
        $this->assertType('Lisphp_Runtime_List_Map', $scope['map']);
        $this->assertType('Lisphp_Runtime_List_Filter', $scope['filter']);
        $this->assertType('Lisphp_Runtime_List_Fold', $scope['fold']);
        $this->assertType('Lisphp_Runtime_Predicate_Eq', $scope['==']);
        $this->assertType('Lisphp_Runtime_Predicate_Eq', $scope['eq']);
        $this->assertType('Lisphp_Runtime_Predicate_Eq', $scope['eq?']);
        $this->assertType('Lisphp_Runtime_Predicate_Equal', $scope['=']);
        $this->assertType('Lisphp_Runtime_Predicate_Equal', $scope['equal']);
        $this->assertType('Lisphp_Runtime_Predicate_Equal', $scope['equal?']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEq', $scope['/==']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEq', $scope['!==']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEq', $scope['not-eq']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEq', $scope['not-eq?']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEqual', $scope['!=']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEqual', $scope['/=']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEqual',
                          $scope['not-equal']);
        $this->assertType('Lisphp_Runtime_Predicate_NotEqual',
                          $scope['not-equal?']);
        foreach (Lisphp_Runtime_Predicate_Type::$types as $type) {
            $this->assertType('Lisphp_Runtime_Predicate_Type',
                              $scope["$type?"]);
            $this->assertEquals($type, $scope["$type?"]->type);
        }
        $this->assertType('Lisphp_Runtime_Predicate_Type', $scope['nil?']);
        $this->assertEquals('null', $scope['nil?']->type);
        $this->assertType('Lisphp_Runtime_Predicate_IsA', $scope['is-a?']);
        $this->assertType('Lisphp_Runtime_Predicate_IsA', $scope['isa?']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Addition', $scope['+']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Subtraction', $scope['-']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Multiplication',
                          $scope['*']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Division', $scope['/']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Modulus', $scope['%']);
        $this->assertType('Lisphp_Runtime_Arithmetic_Modulus', $scope['mod']);
        $this->assertType('Lisphp_Runtime_String_Concat', $scope['.']);
        $this->assertType('Lisphp_Runtime_String_Concat', $scope['concat']);
        $this->assertType('Lisphp_Runtime_String_StringJoin',
                          $scope['string-join']);
        $this->assertType('Lisphp_Runtime_PHPFunction', $scope['substring']);
        $this->assertEquals('substr', $scope['substring']->callback);
        $this->assertType('Lisphp_Runtime_PHPFunction',
                          $scope['string-upcase']);
        $this->assertEquals('strtoupper', $scope['string-upcase']->callback);
        $this->assertType('Lisphp_Runtime_PHPFunction',
                          $scope['string-downcase']);
        $this->assertEquals('strtolower', $scope['string-downcase']->callback);
        $this->assertType('Lisphp_Runtime_Logical_Not', $scope['not']);
        $this->assertType('Lisphp_Runtime_Logical_And', $scope['and']);
        $this->assertType('Lisphp_Runtime_Logical_Or', $scope['or']);
        $this->assertType('Lisphp_Runtime_Logical_If', $scope['if']);
    }

    function testFull() {
        $scope = Lisphp_Environment::full();
        $this->testSandbox($scope);
        $this->assertType('Lisphp_Runtime_Use', $scope['use']);
    }
}

