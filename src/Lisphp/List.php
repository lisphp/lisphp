<?php

class Lisphp_List extends ArrayObject implements Lisphp_Form
{
    public function evaluate(Lisphp_Scope $scope)
    {
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
        throw new Lisphp_InvalidApplicationException($function, $this);
    }

    public function car()
    {
        return isset($this[0]) ? $this[0] : null;
    }

    public function cdr()
    {
        if (!isset($this[0])) return;

        return new self(array_slice($this->getArrayCopy(), 1));
    }

    public function __toString()
    {
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
