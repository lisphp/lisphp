<?php

final class Lisphp_Quote implements Lisphp_Form
{
    public $form;

    public function __construct(Lisphp_Form $form)
    {
        $this->form = $form;
    }

    public function evaluate(Lisphp_Scope $scope)
    {
        return $this->form;
    }

    public function __toString()
    {
        return ':' . $this->form->__toString();
    }
}
