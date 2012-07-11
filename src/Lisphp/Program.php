<?php

final class Lisphp_Program implements IteratorAggregate, ArrayAccess,
                                      Countable {
    public $forms;

    public static function load($file)
    {
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

    public function __construct($program)
    {
        $this->forms = Lisphp_Parser::parse($program, true);
    }

    public function execute(Lisphp_Scope $scope)
    {
        foreach ($this->forms as $form) {
            $form->evaluate($scope);
        }
    }

    public function offsetGet($offset)
    {
        return $this->forms[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->forms[$offset]);
    }

    public function offsetSet($_, $__)
    {
        throw new BadMethodCallException('Lisphp_Program object is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Lisphp_Program object is immutable');
    }

    public function getIterator()
    {
        return new ArrayIterator($this->forms);
    }

    public function count()
    {
        return count($this->forms);
    }
}
