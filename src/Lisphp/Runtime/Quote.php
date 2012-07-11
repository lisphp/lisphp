<?php

final class Lisphp_Runtime_Quote implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        return $arguments[0];
    }
}

