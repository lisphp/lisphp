<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime/PHPFunction.php';
require_once 'Lisphp/Runtime/PHPClass.php';

final class Lisphp_Runtime_Use implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        $values = array();
        foreach ($arguments as $name) {
            list($name, $value) = $this->dispatch($name);
            $scope->let($name, $value);
            $values[] = $value;
        }
        return new Lisphp_List($values);
    }

    function dispatch(Lisphp_Form $name) {
        if ($name instanceof Lisphp_Symbol) {
            $phpname = $name = $name->symbol;
        } else {
            $phpname = $name[0]->symbol;
            $name = $name[1]->symbol;
        }
        $phpname = str_replace('-', '_', $phpname);
        if (preg_match('/^<([^>]+)>$/', $phpname, $matches)) {
            $phpname = str_replace('/', '_', $matches[1]);
            return array($name, new Lisphp_Runtime_PHPClass($phpname));
        }
        return array($name, new Lisphp_Runtime_PHPFunction($phpname));
    }
}

