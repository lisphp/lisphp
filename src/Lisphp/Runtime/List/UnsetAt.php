<?php

final class Lisphp_Runtime_List_UnsetAt extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($list, $key) = $arguments;
        if (isset($list[$key])) {
            $value = $list[$key];
            unset($list[$key]);
            return $value;
        }
        $key = var_export($key, true);
        throw new OutOfRangeException("no index $key of the list");
    }
}
