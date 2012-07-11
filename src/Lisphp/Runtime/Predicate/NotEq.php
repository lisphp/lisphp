<?php

class Lisphp_Runtime_Predicate_NotEq extends Lisphp_Runtime_ComparingPredicate {
    protected $logicalOr = true;

    protected function compare($a, $b) {
        return $a !== $b;
    }
}
