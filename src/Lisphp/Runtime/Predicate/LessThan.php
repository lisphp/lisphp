<?php

final class Lisphp_Runtime_Predicate_LessThan
      extends Lisphp_Runtime_ComparingPredicate {
    protected function compare($a, $b) {
        return $a < $b;
    }
}
