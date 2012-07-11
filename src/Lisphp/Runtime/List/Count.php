<?php

final class Lisphp_Runtime_List_Count extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($list) = $arguments;
        return is_string($list) ? strlen($list) : count($list);
    }
}
