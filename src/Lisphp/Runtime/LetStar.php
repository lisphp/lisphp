<?php

final class Lisphp_Runtime_LetStar implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        $vars = $arguments->car();
        $scope = new Lisphp_Scope($scope);
        foreach ($vars as $var) {
            list($var, $value) = $var;
            $scope->let($var, $value->evaluate($scope));
        }
        foreach ($arguments->cdr() as $form) {
            $retval = $form->evaluate($scope);
        }

        return $retval;
    }
}
