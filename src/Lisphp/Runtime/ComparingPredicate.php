<?php

abstract class Lisphp_Runtime_ComparingPredicate
         extends Lisphp_Runtime_BuiltinFunction {
    protected $logicalOr = false;

    final protected function execute(array $operands)
    {
        $c = count($operands);
        if (2 > $c) throw new InvalidArgumentException('too few operands');
        $fst = array_shift($operands);
        $or = $this->logicalOr;
        foreach ($operands as $val) {
            if ($or xor !$this->compare($fst, $val)) return $or;
            $fst = $val;
        }

        return !$or;
    }

    abstract protected function compare($a, $b);
}
