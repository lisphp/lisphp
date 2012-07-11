<?php

final class Lisphp_Runtime_Quote implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        return $arguments[0];
    }
}
