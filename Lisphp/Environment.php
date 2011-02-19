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
        $scope['setf!'] = new Lisphp_Runtime_Setf;
        $scope['let'] = new Lisphp_Runtime_Let;
        $scope['macro'] = new Lisphp_Runtime_Macro;
        $scope['lambda'] = new Lisphp_Runtime_Lambda;
        $scope['apply'] = new Lisphp_Runtime_Apply;
        $scope['do'] = new Lisphp_Runtime_Do;
        $scope['dict'] = new Lisphp_Runtime_Dict;
        $scope['array'] = new Lisphp_Runtime_Array;
        $scope['list'] = new Lisphp_Runtime_List;
        $scope['car'] = new Lisphp_Runtime_List_Car;
        $scope['cdr'] = new Lisphp_Runtime_List_Cdr;
        $scope['at'] = new Lisphp_Runtime_List_At;
        $scope['set-at!'] = new Lisphp_Runtime_List_SetAt;
        $scope['unset-at!'] = new Lisphp_Runtime_List_UnsetAt;
        $scope['exists-at?'] = new Lisphp_Runtime_List_ExistsAt;
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
        $scope['<'] = new Lisphp_Runtime_Predicate_LessThan;
        $scope['>'] = new Lisphp_Runtime_Predicate_GreaterThan;
        $scope['<='] = new Lisphp_Runtime_Predicate_LessEqual;
        $scope['>='] = new Lisphp_Runtime_Predicate_GreaterEqual;
        foreach (Lisphp_Runtime_Predicate_Type::getFunctions() as $n => $f) {
            $scope[$n] = $f;
        }
        $scope['isa?'] = $scope['is-a?'] = new Lisphp_Runtime_Predicate_IsA;
        $scope['+'] = new Lisphp_Runtime_Arithmetic_Addition;
        $scope['-'] = new Lisphp_Runtime_Arithmetic_Subtraction;
        $scope['*'] = new Lisphp_Runtime_Arithmetic_Multiplication;
        $scope['/'] = new Lisphp_Runtime_Arithmetic_Division;
        $scope['%'] = $scope['mod'] =new Lisphp_Runtime_Arithmetic_Modulus;
        $scope['string'] = new Lisphp_Runtime_PHPFunction('strval');
        $scope['.'] = $scope['concat'] =new Lisphp_Runtime_String_Concat;
        $scope['string-join'] = new Lisphp_Runtime_String_StringJoin;
        $scope['substring'] = new Lisphp_Runtime_PHPFunction('substr');
        $scope['string-upcase'] = new Lisphp_Runtime_PHPFunction('strtoupper');
        $scope['string-downcase']= new Lisphp_Runtime_PHPFunction('strtolower');
        $scope['not'] = new Lisphp_Runtime_Logical_Not;
        $scope['and'] = new Lisphp_Runtime_Logical_And;
        $scope['or'] = new Lisphp_Runtime_Logical_Or;
        $scope['if'] = new Lisphp_Runtime_Logical_If;
        $scope['->'] = new Lisphp_Runtime_Object_GetAttribute;
        return $scope;
    }

    static function full() {
        $scope = new Lisphp_Scope(self::sandbox());
        $scope->let('use', new Lisphp_Runtime_Use);
        $scope->let('from', new Lisphp_Runtime_From);
        $scope->let('*env*', $_ENV);
        $scope->let('*server*', $_SERVER);
        return $scope;
    }

    protected static $antimagicFunction = null;

    protected static function antimagic($vars) {
        if (!get_magic_quotes_gpc()) return ($vars);
        if (!$f = self::$antimagicFunction) {
            self::$antimagicFunction = create_function('$vars', '
                return is_array($vars)
                     ? array_map(' . __CLASS__ . '::$antimagicFunction, $vars)
                     : stripslashes($vars);
            ');
        }
        return $f($vars);
    }

    static function webapp() {
        $scope = new Lisphp_Scope(self::sandbox());
        $scope->let('*get*', self::antimagic($_GET));
        $scope->let('*post*', self::antimagic($_POST));
        $scope->let('*request*', self::antimagic($_REQUEST));
        $scope->let('*files*', $_FILES);
        $scope->let('*cookie*', self::antimagic($_COOKIE));
        $scope->let('*session*', $_SESSION);
    }
}

