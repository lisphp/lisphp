<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime/Function.php';

class Lisphp_Runtime_Function implements Lisphp_Applicable {
    public $scope, $parameters, $body;

    function __construct(Lisphp_Scope $scope,
                         Lisphp_List $parameters,
                         Lisphp_Form $body) {
        $this->scope = $scope;
        $this->parameters = $parameters;
        $this->body = $body;
    }

    final function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        $args = array();
        foreach ($arguments as $arg) {
            $args[] = $arg->evaluate($scope);
        }
        return $this->execute($args);
    }

    protected function execute(array $arguments) {
        $local = new Lisphp_Scope($this->scope);
        foreach ($this->parameters as $i => $name) {
            $local->let($name, $arguments[$i]);
        }
        return $this->body->evaluate($local);
    }
}

