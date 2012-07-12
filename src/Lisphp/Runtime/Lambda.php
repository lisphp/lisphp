<?php

final class Lisphp_Runtime_Lambda implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        if (count($arguments) < 2) {
            $msg = 'parameter list and body form are required';
            throw new InvalidArgumentException($msg);
        }

        return new Lisphp_Runtime_Function($scope,
                                           $arguments->car(),
                                           $arguments->cdr());
    }
}
