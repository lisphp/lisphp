<?php
require_once 'Lisphp/Runtime/Function.php';

final class Lisphp_Runtime_PHPFunction extends Lisphp_Runtime_Function {
    public $callback;

    function __construct($callback) {
        $this->callback = $callback;
    }

    function execute(array $arguments) {
        return call_user_func_array($this->callback, $arguments);
    }
}

