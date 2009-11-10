<?php
require_once 'Lisphp/Form.php';
require_once 'Lisphp/Scope.php';

final class Lisphp_Symbol implements Lisphp_Form {
    public $symbol;

    function __construct($symbol) {
        if (!is_string($symbol)) {
            throw new UnexpectedValueException('expected string');
        }
        $this->symbol = $symbol;
    }

    function evaluate(Lisphp_Scope $scope) {
        return $scope[$this];
    }

    function __toString() {
        return $this->symbol;
    }
}

