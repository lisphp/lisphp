<?php
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime.php';

final class Lisphp_Environment {
    static function sandbox() {
        $scope = new Lisphp_Scope;
        $scope['eval'] = new Lisphp_Runtime_Eval;
        $scope['quote'] = new Lisphp_Runtime_Quote;
        $scope['define'] = new Lisphp_Runtime_Define;
        $scope['let'] = new Lisphp_Runtime_Let;
        $scope['lambda'] = new Lisphp_Runtime_Lambda;
        $scope['apply'] = new Lisphp_Runtime_Apply;
        $scope['car'] = new Lisphp_Runtime_List_Car;
        $scope['cdr'] = new Lisphp_Runtime_List_Cdr;
        $scope['+'] = new Lisphp_Runtime_Arithmetic_Addition;
        $scope['-'] = new Lisphp_Runtime_Arithmetic_Subtraction;
        $scope['*'] = new Lisphp_Runtime_Arithmetic_Multiplication;
        $scope['/'] = new Lisphp_Runtime_Arithmetic_Division;
        $scope['%'] = new Lisphp_Runtime_Arithmetic_Modulus;
        $scope['not'] = new Lisphp_Runtime_Logical_Not;
        $scope['and'] = new Lisphp_Runtime_Logical_And;
        $scope['or'] = new Lisphp_Runtime_Logical_Or;
        $scope['if'] = new Lisphp_Runtime_Logical_If;
        return $scope;
    }
}

