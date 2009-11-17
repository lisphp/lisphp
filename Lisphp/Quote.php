<?php
require_once 'Lisphp/Form.php';
require_once 'Lisphp/Scope.php';

final class Lisphp_Quote implements Lisphp_Form {
    public $form;

    function __construct(Lisphp_Form $form) {
        $this->form = $form;
    }

    function evaluate(Lisphp_Scope $scope) {
        return $this->form;
    }

    function __toString() {
        return ':' . $this->form->__toString();
    }
}

