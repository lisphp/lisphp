<?php

class Lisphp_Runtime_Logical_Not extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        return !$arguments[0];
    }
}
