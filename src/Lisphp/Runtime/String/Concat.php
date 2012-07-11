<?php

final class Lisphp_Runtime_String_Concat
      extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments)
    {
        if (isset($arguments[0])) return join('', $arguments);
        throw new InvalidArgumentException('too few strings');
    }
}
