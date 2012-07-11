<?php

class Lisphp_Runtime_Arithmetic_Addition
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return array_sum($arguments);
    }
}

class Lisphp_Runtime_Arithmetic_Subtraction
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
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

class Lisphp_Runtime_Arithmetic_Multiplication
    extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return count($arguments) < 1 ? 1 : array_product($arguments);
    }
}

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

class Lisphp_Runtime_Arithmetic_Modulus extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        if (isset($arguments[1])) return $arguments[0] % $arguments[1];
        throw new InvalidArgumentException('2 arguments are required');
    }
}

