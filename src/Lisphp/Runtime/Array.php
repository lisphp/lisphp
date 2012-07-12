<?php

final class Lisphp_Runtime_Array extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        return $arguments;
    }
}
