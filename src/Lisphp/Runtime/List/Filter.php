<?php

final class Lisphp_Runtime_List_Filter extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        list($predicate, $values) = $arguments;
        $list = array();
        foreach ($values as $value) {
            if (Lisphp_Runtime_Function::call($predicate, array($value))) {
                $list[] = $value;
            }
        }

        return new Lisphp_List($list);
    }
}
