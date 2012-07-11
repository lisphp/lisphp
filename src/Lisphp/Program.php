<?php

final class Lisphp_Program implements IteratorAggregate, ArrayAccess,
                                      Countable {
    public $forms;

    static function load($file) {
        if ($fp = fopen($file, 'r')) {
            for ($code = ''; !feof($fp); $code .= fread($fp, 8192));
            fclose($fp);
            try {
                $program = new self($code);
            } catch (Lisphp_ParsingException $e) {
                throw new Lisphp_ParsingException($e->code, $e->offset, $file);
            }
            return $program;
        }
    }

    function __construct($program) {
        $this->forms = Lisphp_Parser::parse($program, true);
    }

    function execute(Lisphp_Scope $scope) {
        foreach ($this->forms as $form) {
            $form->evaluate($scope);
        }
    }

    function offsetGet($offset) {
        return $this->forms[$offset];
    }

    function offsetExists($offset) {
        return isset($this->forms[$offset]);
    }

    function offsetSet($_, $__) {
        throw new BadMethodCallException('Lisphp_Program object is immutable');
    }

    function offsetUnset($offset) {
        throw new BadMethodCallException('Lisphp_Program object is immutable');
    }

    function getIterator() {
        return new ArrayIterator($this->forms);
    }

    function count() {
        return count($this->forms);
    }
}
