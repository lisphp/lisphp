<?php
require_once 'Lisphp/Runtime/BuiltinFunction.php';

abstract class Lisphp_Runtime_Predicate extends Lisphp_Runtime_BuiltinFunction {
    protected $logicalOr = false;

    final protected function execute(array $operands) {
        $c = count($operands);
        if (2 > $c) throw new InvalidArgumentException('too few operands');
        $fst = array_shift($operands);
        $or = $this->logicalOr;
        foreach ($operands as $val) {
            if ($or xor !$this->compare($val, $fst)) return $or;
            $fst = $val;
        }
        return !$or;
    }

    abstract protected function compare($a, $b);
}

class Lisphp_Runtime_Predicate_Eq extends Lisphp_Runtime_Predicate {
    protected function compare($a, $b) {
        return $a === $b;
    }
}

class Lisphp_Runtime_Predicate_Equal extends Lisphp_Runtime_Predicate {
    protected function compare($a, $b) {
        return $a == $b;
    }
}

class Lisphp_Runtime_Predicate_NotEq extends Lisphp_Runtime_Predicate {
    protected $logicalOr = true;

    protected function compare($a, $b) {
        return $a !== $b;
    }
}

class Lisphp_Runtime_Predicate_NotEqual extends Lisphp_Runtime_Predicate {
    protected $logicalOr = true;

    protected function compare($a, $b) {
        return $a != $b;
    }
}

