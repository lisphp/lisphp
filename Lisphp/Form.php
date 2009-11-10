<?php
require_once 'Lisphp/Scope.php';

interface Lisphp_Form {
    function evaluate(Lisphp_Scope $scope);
}

