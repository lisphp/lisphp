<?php

final class Lisphp_Runtime_Setf implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        $name = $arguments[0];
        if ($name instanceof Lisphp_Symbol) {
            $retval = $arguments[1]->evaluate($scope);
        } else {
            throw new InvalidArgumentException(
                'first operand of setf! form must be symbol'
            );
        }
        return $scope[$name] = $retval;
    }
}
