<?php

final class Lisphp_Runtime_Eval implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $args) {
        $form = $args[0]->evaluate($scope);
        return $form->evaluate(isset($args[1]) ? $args[1]->evaluate($scope)
                                               : $scope);
    }
}
