<?php

class Lisphp_Runtime_Arithmetic_Subtraction
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments)
    {
        if (1 == $c = count($arguments)) return -$arguments[0];
        else if ($c < 1) {
            throw new InvalidArgumentException('least 1 argument is required');
        }
        foreach ($arguments as $value) {
            if (isset($result)) $result -= $value;
            else $result = $value;
        }

        return $result;
    }
}
