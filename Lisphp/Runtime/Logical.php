<?php
require_once 'Lisphp/Runtime/BuiltinFunction.php';

class Lisphp_Runtime_Logical_Not extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return !$arguments[0];
    }
}

class Lisphp_Runtime_Logical_And extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        foreach ($arguments as $value) {
            if (!$value) return $value;
        }
        return $value;
    }
}

class Lisphp_Runtime_Logical_Or extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        foreach ($arguments as $value) {
            if ($value) return $value;
        }
        return $value;
    }
}

final class Lisphp_Runtime_Logical_If implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $args) {
        return $args[$args[0]->evaluate($scope) ? 1 : 2]->evaluate($scope);
    }
}

