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
            try {
                return call_user_func_array($function, $parameters);
            } catch (Exception $e) {
                // $message will end up being a "stack trace" of Lisp forms.
                $message = $e->getMessage() . "\n# " . substr($this->__toString(), 0, 100);
                // Set the previous exception to the original (innermost) exception.
                // This will cause two stack traces to be logged: the original
                // exception and the "Lisp stack trace".
                throw new Exception($message, 0, $e->getPrevious() ? $e->getPrevious() : $e);
            }
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
