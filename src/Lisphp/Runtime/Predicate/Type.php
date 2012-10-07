<?php

final class Lisphp_Runtime_Predicate_Type
      extends Lisphp_Runtime_BuiltinFunction {
    public static $types = array('array', 'binary', 'bool', 'buffer', 'double',
                          'float', 'int', 'integer', 'long', 'null', 'numeric',
                          'object', 'real', 'resource', 'scalar', 'string');
    public $type;

    public static function getFunctions(Lisphp_Scope $superscope = null)
    {
        $scope = new Lisphp_Scope($superscope);
        foreach (self::$types as $type) {
            $scope["$type?"] = new self($type);
        }
        $scope['nil?'] = new self('null');

        return $scope;
    }

    public function __construct($type)
    {
        $this->type = $type;
    }

    protected function execute(array $arguments)
    {
        $function = "is_{$this->type}";
        foreach ($arguments as $value) {
            if (!$function($value)) return false;
        }

        return true;
    }
}
