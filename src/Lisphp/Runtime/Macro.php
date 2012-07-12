<?php

final class Lisphp_Runtime_Macro implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        return new Lisphp_Runtime_UserMacro($scope, $arguments);
    }
}
