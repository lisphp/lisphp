<?php

final class Lisphp_Runtime_Predicate_LessEqual
      extends Lisphp_Runtime_ComparingPredicate {
    protected function compare($a, $b)
    {
        return $a <= $b;
    }
}
