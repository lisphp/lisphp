<?php

class Lisphp_Runtime_Arithmetic_Division
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        if (isset($arguments[0])) {
            foreach ($arguments as $value) {
                if (isset($result)) $result /= $value;
                else $result = $value;
            }
            return $result;
        }
        throw new InvalidArgumentException('least 1 argument is required');
    }
}
