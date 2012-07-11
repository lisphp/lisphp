<?php

class Lisphp_List extends ArrayObject implements Lisphp_Form {
    function evaluate(Lisphp_Scope $scope) {
        $function = $this->car()->evaluate($scope);
        $applicable = $function instanceof Lisphp_Applicable;
        if (is_callable($function) && is_object($function)) {
            $parameters = array();
            foreach ($this->cdr() as $arg) {
                $parameters[] = $arg->evaluate($scope);
            }
            return call_user_func_array($function, $parameters);
        }
        if ($applicable) return $function->apply($scope, $this->cdr());
        throw new InvalidApplicationException($function, $this);
    }

    function car() {
        return isset($this[0]) ? $this[0] : null;
    }

    function cdr() {
        if (!isset($this[0])) return;
        return new self(array_slice($this->getArrayCopy(), 1));
    }

    function __toString() {
        foreach ($this as $form) {
            if ($form instanceof Lisphp_Form) {
                $strs[] = $form->__toString();
            } else {
                $strs[] = '...';
            }
        }
        return '(' . join(' ', $strs) . ')';
    }
}

class InvalidApplicationException extends BadFunctionCallException {
    public $valueToApply;

    function __construct($valueToApply, Lisphp_List $list = null) {
        $this->valueToApply = $valueToApply;
        $this->list = $list;
        $type = is_object($this->valueToApply)
              ? get_class($this->valueToApply)
              : (is_null($this->valueToApply) ? 'nil'
                                              : gettype($this->valueToApply));
        $msg = "$type cannot be applied; see Lisphp_Applicable interface";
        if ($list) {
            $msg .= ': ' . $list->__toString();
        }
        parent::__construct($msg);
    }
}
