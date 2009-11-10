<?php
require_once 'Lisphp/Form.php';
require_once 'Lisphp/Scope.php';

final class Lisphp_Literal implements Lisphp_Form {
    public $value;

    function __construct($value) {
        if (!in_array(gettype($value), array('integer', 'double', 'string'))) {
            $msg = 'it accepts only numbers or strings';
            throw new UnexpectedValueException($msg);
        }
        $this->value = $value;
    }

    function evaluate(Lisphp_Scope $scope) {
        return $this->value;
    }

    function isInteger() {
        return is_int($this->value);
    }

    function isReal() {
        return is_float($this->value);
    }

    function isString() {
        return is_string($this->value);
    }

    function __toString() {
        return var_export($this->value, true);
    }
}

