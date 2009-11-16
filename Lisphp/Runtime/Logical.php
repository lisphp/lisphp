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

