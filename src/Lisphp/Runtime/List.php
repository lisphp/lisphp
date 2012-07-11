<?php

final class Lisphp_Runtime_List extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        return new Lisphp_List($arguments);
    }
}
