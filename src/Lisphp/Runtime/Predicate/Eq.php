<?php

class Lisphp_Runtime_Predicate_Eq extends Lisphp_Runtime_ComparingPredicate
{
    protected function compare($a, $b)
    {
        return $a === $b;
    }
}
