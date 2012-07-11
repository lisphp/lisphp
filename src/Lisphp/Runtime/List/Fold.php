<?php

final class Lisphp_Runtime_List_Fold extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        list($aggregate, $values) = $arguments;
        if ($hasResult = isset($arguments[2])) {
            $result = $arguments[2];
        }
        foreach ($values as $value) {
            $result = $hasResult
                    ? Lisphp_Runtime_Function::call($aggregate,
                                                    array($result, $value))
                    : $value;
            $hasResult = true;
        }
        if ($hasResult) return $result;
        throw new InvalidArgumentException(
            'the initial value or one or more elements of the list are required'
        );
    }
}
