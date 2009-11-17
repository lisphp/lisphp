<?php
require_once 'Lisphp/Runtime/BuiltinFunction.php';

final class Lisphp_Runtime_Apply extends Lisphp_Runtime_BuiltinFunction {
    function execute(array $arguments) {
        list($func, $args) = $arguments;
        if ($func instanceof Lisphp_Runtime_BuiltinFunction) {
            return $func->execute($args->getArrayCopy());
        }
    }
}

