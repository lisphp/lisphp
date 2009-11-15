<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';

final class Lisphp_Runtime_Define implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        $scope[$arguments[0]] = $retval = $arguments[1]->evaluate($scope);
        return $retval;
    }
}

