<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime/Function.php';

final class Lisphp_Runtime_Lambda implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        if (count($arguments) < 2) {
            $msg = 'parameter list and body form are required';
            throw new InvalidArgumentException($msg);
        }
        list($params, $body) = $arguments;
        return new Lisphp_Runtime_Function($scope, $params, $body);
    }
}

