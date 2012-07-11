<?php

final class Lisphp_Runtime_List_At extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        list($list, $offset) = $arguments;
        if (isset($list[$offset])) return $list[$offset];
        $offset = var_export($offset, true);
        throw new OutOfRangeException("no index $offset of the list");
    }
}
