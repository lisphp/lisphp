<?php
require_once 'Lisphp/Runtime/Function.php';

final class Lisphp_Runtime_PHPClass extends Lisphp_Runtime_Function {
    public $class;

    function __construct($class) {
        try {
            $this->class = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new UnexpectedValueException($e);
        }
    }

    function execute(array $arguments) {
        return $this->class->newInstanceArgs($arguments);
    }
}

