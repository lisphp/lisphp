<?php
require_once 'Lisphp/Runtime/BuiltinFunction.php';

abstract class Lisphp_Runtime_ComparingPredicate
         extends Lisphp_Runtime_BuiltinFunction {
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

class Lisphp_Runtime_Predicate_Eq extends Lisphp_Runtime_ComparingPredicate {
    protected function compare($a, $b) {
        return $a === $b;
    }
}

class Lisphp_Runtime_Predicate_Equal extends Lisphp_Runtime_ComparingPredicate {
    protected function compare($a, $b) {
        return $a == $b;
    }
}

class Lisphp_Runtime_Predicate_NotEq extends Lisphp_Runtime_ComparingPredicate {
    protected $logicalOr = true;

    protected function compare($a, $b) {
        return $a !== $b;
    }
}

class Lisphp_Runtime_Predicate_NotEqual
    extends Lisphp_Runtime_ComparingPredicate {
    protected $logicalOr = true;

    protected function compare($a, $b) {
        return $a != $b;
    }
}

final class Lisphp_Runtime_Predicate_Type
      extends Lisphp_Runtime_BuiltinFunction {
    static $types = array('array', 'binary', 'bool', 'buffer', 'double',
                          'float', 'int', 'integer', 'long', 'null', 'numberic',
                          'object', 'real', 'resource', 'scalar', 'string');
    public $type;

    static function getFunctions(Lisphp_Scope $superscope = null) {
        $scope = new Lisphp_Scope($superscope);
        foreach (self::$types as $type) {
            $scope["$type?"] = new self($type);
        }
        $scope['nil?'] = new self('null');
        return $scope;
    }

    function __construct($type) {
        $this->type = $type;
    }

    protected function execute(array $arguments) {
        $function = "is_{$this->type}";
        foreach ($arguments as $value) {
            if (!$function($value)) return false;
        }
        return true;
    }
}

final class Lisphp_Runtime_Predicate_IsA
      extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        $object = array_shift($arguments);
        if (!isset($arguments[0])) {
            throw new InvalidArgumentException('too few arguments');
        }
        foreach ($arguments as $class) {
            if (!($class instanceof Lisphp_Runtime_PHPClass)) {
                throw new InvalidArgumentException('only classes are accepted');
            }
            if ($class->isClassOf($object)) return true;
        }
        return false;
    }
}

