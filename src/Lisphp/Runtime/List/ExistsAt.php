<?php

final class Lisphp_Runtime_List_ExistsAt
      extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($list, $key) = $arguments;
        return isset($list[$key]);
    }
}
