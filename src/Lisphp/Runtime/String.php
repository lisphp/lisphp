<?php

final class Lisphp_Runtime_String_Concat
      extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        if (isset($arguments[0])) return join('', $arguments);
        throw new InvalidArgumentException('too few strings');
    }
}

final class Lisphp_Runtime_String_StringJoin
      extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($strs, $sep) = $arguments;
        if (is_array($strs)) return join($sep, $strs);
        foreach ($strs as $s) {
            if (isset($result)) {
                $result .= $sep . $s;
            } else {
                $result = $s;
            }
        }
        return $result;
    }
}

