<?php

class Lisphp_Runtime_Function implements Lisphp_Applicable
{
    public $scope, $parameters, $body;

    public static function call($func, array $args)
    {
        if ($func instanceof self) return $func->execute($args);
        else if (is_callable($func) && is_object($func)) {
            return call_user_func_array($func, $args);
        }
        throw new InvalidArgumentException('expected callable value');
    }

    public function __construct(Lisphp_Scope $scope,
                         Lisphp_List $parameters,
                         Lisphp_Form $body) {
        $this->scope = $scope;
        $this->parameters = $parameters;
        $this->body = $body;
    }

    final public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        $args = array();
        foreach ($arguments as $arg) {
            $args[] = $arg->evaluate($scope);
        }

        return $this->execute($args);
    }

    protected function execute(array $arguments)
    {
        $local = new Lisphp_Scope($this->scope);
        foreach ($this->parameters as $i => $name) {
            if (!isset($arguments[$i])) {
                throw new InvalidArgumentException('too few arguments');
            }
            $local->let($name, $arguments[$i]);
        }
        $local->let('#arguments', new Lisphp_List($arguments));
        foreach ($this->body as $form) {
            $retval = $form->evaluate($local);
        }

        return $retval;
    }
}
