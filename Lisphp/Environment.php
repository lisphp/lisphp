<?php
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime.php';

final class Lisphp_Environment {
    static function sandbox() {
        $scope = new Lisphp_Scope;
        $scope['nil'] = null;
        $scope['true'] = $scope['#t'] = true;
        $scope['false'] = $scope['#f'] = false;
        $scope['eval'] = new Lisphp_Runtime_Eval;
        $scope['quote'] = new Lisphp_Runtime_Quote;
        $scope['symbol'] = new Lisphp_Runtime_PHPFunction(
            array('Lisphp_Symbol', 'get')
        );
        $scope['define'] = new Lisphp_Runtime_Define;
        $scope['let'] = new Lisphp_Runtime_Let;
        $scope['macro'] = new Lisphp_Runtime_Macro;
        $scope['lambda'] = new Lisphp_Runtime_Lambda;
        $scope['apply'] = new Lisphp_Runtime_Apply;
        $scope['dict'] = new Lisphp_Runtime_Dict;
        $scope['array'] = new Lisphp_Runtime_Array;
        $scope['list'] = new Lisphp_Runtime_List;
        $scope['car'] = new Lisphp_Runtime_List_Car;
        $scope['cdr'] = new Lisphp_Runtime_List_Cdr;
        $scope['at'] = new Lisphp_Runtime_List_At;
        $scope['count'] = new Lisphp_Runtime_List_Count;
        $scope['map'] = new Lisphp_Runtime_List_Map;
        $scope['filter'] = new Lisphp_Runtime_List_Filter;
        $scope['fold'] = new Lisphp_Runtime_List_Fold;
        $scope['=='] = $scope['eq'] = $scope['eq?']
                     = new Lisphp_Runtime_Predicate_Eq;
        $scope['='] = $scope['equal'] = $scope['equal?']
                    = new Lisphp_Runtime_Predicate_Equal;
        $scope['!=='] = $scope['/=='] = $scope['not-eq'] = $scope['not-eq?']
                      = new Lisphp_Runtime_Predicate_NotEq;
        $scope['!='] = $scope['/='] = $scope['not-equal'] = $scope['not-equal?']
                     = new Lisphp_Runtime_Predicate_NotEqual;
        foreach (Lisphp_Runtime_Predicate_Type::getFunctions() as $n => $f) {
            $scope[$n] = $f;
        }
        $scope['isa?'] = $scope['is-a?'] = new Lisphp_Runtime_Predicate_IsA;
        $scope['+'] = new Lisphp_Runtime_Arithmetic_Addition;
        $scope['-'] = new Lisphp_Runtime_Arithmetic_Subtraction;
        $scope['*'] = new Lisphp_Runtime_Arithmetic_Multiplication;
        $scope['/'] = new Lisphp_Runtime_Arithmetic_Division;
        $scope['%'] = $scope['mod'] =new Lisphp_Runtime_Arithmetic_Modulus;
        $scope['.'] = $scope['concat'] =new Lisphp_Runtime_String_Concat;
        $scope['string-join'] =new Lisphp_Runtime_String_StringJoin;
        $scope['not'] = new Lisphp_Runtime_Logical_Not;
        $scope['and'] = new Lisphp_Runtime_Logical_And;
        $scope['or'] = new Lisphp_Runtime_Logical_Or;
        $scope['if'] = new Lisphp_Runtime_Logical_If;
        return $scope;
    }

    static function full() {
        $scope = new Lisphp_Scope(self::sandbox());
        $scope['use'] = new Lisphp_Runtime_Use;
        return $scope;
    }
}

