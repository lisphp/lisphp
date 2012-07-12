<?php

final class Lisphp_Runtime_List_SetAt extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        list($list, $offset) = $arguments;
        $list = array_shift($arguments);
        if (count($arguments) < 2) {
            $list[] = $value = array_shift($arguments);
        } else {
            list($key, $value) = $arguments;
            $list[$key] = $value;
        }

        return $value;
    }
}
