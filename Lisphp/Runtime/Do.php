<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';

final class Lisphp_Runtime_Do extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return $arguments[count($arguments) - 1];
    }
}

