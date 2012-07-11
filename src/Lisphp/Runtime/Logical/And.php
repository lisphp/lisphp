<?php

class Lisphp_Runtime_Logical_And implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $operands) {
        foreach ($operands as $form) {
            if (!$value = $form->evaluate($scope)) return $value;
        }
        return $value;
    }
}
