<?php
require_once 'Lisphp/Runtime/BuiltinFunction.php';
require_once 'Lisphp/Runtime/PHPFunction.php';

final class Lisphp_Runtime_Object_GetAttribute implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        if (count($arguments) < 2) {
            throw new InvalidArgumentException('expected least two arguments');
        }
        $object = $first = $arguments->car()->evaluate($scope);
        $names = $arguments->cdr();
        $chain = '';
        foreach ($names as $name) {
            $name = (string) $name;
            $chain .= "->$name";
            if (isset($object->$name)) {
                $object = $object->$name;
            } else if (method_exists($object, $name)) {
                $object = new Lisphp_Runtime_PHPFunction(array($object, $name));
            } else {
                $o = (is_object($first) ? get_class($first) : gettype($first))
                   . $chain;
                throw new RuntimeException("there is no name '$name' for $o");
            }
        }
        return $object;
    }
}
