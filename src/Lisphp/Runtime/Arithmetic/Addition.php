<?php

class Lisphp_Runtime_Arithmetic_Addition
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return array_sum($arguments);
    }
}
