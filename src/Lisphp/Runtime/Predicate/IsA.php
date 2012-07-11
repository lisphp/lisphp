<?php

final class Lisphp_Runtime_Predicate_IsA
      extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        $object = array_shift($arguments);
        if (!isset($arguments[0])) {
            throw new InvalidArgumentException('too few arguments');
        }
        foreach ($arguments as $class) {
            if (!($class instanceof Lisphp_Runtime_PHPClass)) {
                throw new InvalidArgumentException('only classes are accepted');
            }
            if ($class->isClassOf($object)) return true;
        }
        return false;
    }
}
