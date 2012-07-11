<?php

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

    function getStaticMethods() {
        $methods = array();
        foreach ($this->class->getMethods() as $method) {
            if (!$method->isStatic() || !$method->isPublic()) continue;
            $name = $method->getName();
            $callback = array($this->class->getName(), $name);
            $methods[$name] = new Lisphp_Runtime_PHPFunction($callback);
        }
        return $methods;
    }

    function isClassOf($instance) {
        return is_object($instance) && $this->class->isInstance($instance);
    }
}
