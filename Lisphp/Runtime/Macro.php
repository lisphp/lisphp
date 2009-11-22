<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime/UserMacro.php';

final class Lisphp_Runtime_Macro implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        return new Lisphp_Runtime_UserMacro($scope, $arguments);
    }
}

