<?php

class Lisphp_Runtime_UserMacro implements Lisphp_Applicable
{
    public $scope, $body;

    public function __construct(Lisphp_Scope $scope, Lisphp_List $body)
    {
        $this->scope = $scope;
        $this->body = $body;
    }

    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        $call = new Lisphp_Scope($this->scope);
        $call->let('#scope', $scope);
        $call->let('#arguments', $arguments);
        foreach ($this->body as $form) {
            $retval = $form->evaluate($call);
        }
        if (isset($retval)) return $retval;
    }
}
