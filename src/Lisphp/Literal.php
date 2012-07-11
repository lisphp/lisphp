<?php

final class Lisphp_Literal implements Lisphp_Form
{
    public $value;

    public function __construct($value)
    {
        if (!in_array(gettype($value), array('integer', 'double', 'string'))) {
            $msg = 'it accepts only numbers or strings';
            throw new UnexpectedValueException($msg);
        }
        $this->value = $value;
    }

    public function evaluate(Lisphp_Scope $scope)
    {
        return $this->value;
    }

    public function isInteger()
    {
        return is_int($this->value);
    }

    public function isReal()
    {
        return is_float($this->value);
    }

    public function isString()
    {
        return is_string($this->value);
    }

    public function __toString()
    {
        return var_export($this->value, true);
    }
}
