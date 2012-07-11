<?php

class Lisphp_Runtime_Arithmetic_Multiplication
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return count($arguments) < 1 ? 1 : array_product($arguments);
    }
}
