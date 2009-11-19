<?php
require_once 'Lisphp/Runtime/Function.php';

final class Lisphp_Runtime_PHPFunction extends Lisphp_Runtime_Function {
    public $callback;

    function __construct($callback) {
        if (is_array($callback) ? !method_exists($callback[0], $callback[1])
                                : !function_exists($callback)) {
            throw new UnexpectedValueException('undefined function or method');
        }
        $this->callback = $callback;
    }

    function execute(array $arguments) {
        return call_user_func_array($this->callback, $arguments);
    }
}

