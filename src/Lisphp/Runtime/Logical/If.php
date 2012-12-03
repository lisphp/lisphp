<?php

final class Lisphp_Runtime_Logical_If implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $args)
    {
        $index = $args[0]->evaluate($scope) ? 1 : 2;
        return isset($args[$index]) ? $args[$index]->evaluate($scope) : null;
    }
}
