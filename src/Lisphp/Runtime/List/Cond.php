<?php

final class Lisphp_Runtime_List_Cond implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        foreach ($arguments as $pair) {
            list($condition, $body) = $pair;
            if ($condition->evaluate($scope)) {
                return $body->evaluate($scope);
            }
        }
    }
}
