<?php
require_once 'Lisphp/Runtime/Function.php';

final class Lisphp_Runtime_PHPClass extends Lisphp_Runtime_Function {
    public $class;

    function __construct($class) {
        $this->class = new ReflectionClass($class);
    }

    function execute(array $arguments) {
        return $this->class->newInstanceArgs($arguments);
    }
}

