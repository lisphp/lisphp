<?php

final class Lisphp_Runtime_Do extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        return $arguments[count($arguments) - 1];
    }
}
