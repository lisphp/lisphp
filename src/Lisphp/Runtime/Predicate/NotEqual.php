<?php

class Lisphp_Runtime_Predicate_NotEqual
    extends Lisphp_Runtime_ComparingPredicate {
    protected $logicalOr = true;

    protected function compare($a, $b)
    {
        return $a != $b;
    }
}
