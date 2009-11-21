<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/List.php';

final class Lisphp_Runtime_Dict implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        $dict = array();
        foreach ($arguments as $pair) {
            if ($pair instanceof Lisphp_List) {
                if (isset($pair[1])) {
                    list($key, $value) = $pair;
                    $dict[$key->evaluate($scope)] = $value->evaluate($scope);
                } else {
                    $dict[] = $pair[0]->evaluate($scope);
                }
            } else {
                $dict[] = $pair->evaluate($scope);
            }
        }
        return $dict;
    }
}
