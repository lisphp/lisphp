<?php

class Lisphp_Runtime_Arithmetic_Modulus extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        if (isset($arguments[1])) return $arguments[0] % $arguments[1];
        throw new InvalidArgumentException('2 arguments are required');
    }
}
