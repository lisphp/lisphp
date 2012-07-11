<?php

final class Lisphp_Symbol implements Lisphp_Form
{
    const PATTERN = '{^
        [^ \s \d () {} \[\] : +-] [^\s () {} \[\] :]*
    |   [+-] ([^ \s \d () {} \[\] :] [^ \s () {} \[\]]*)?
    $}x';

    protected static $map = array();
    public $symbol;

    public static function get($symbol)
    {
        if (isset(self::$map[$symbol])) return self::$map[$symbol];

        return self::$map[$symbol] = new self($symbol);
    }

    protected function __construct($symbol)
    {
        if (!is_string($symbol)) {
            throw new UnexpectedValueException('expected string');
        } elseif (!preg_match(self::PATTERN, $symbol)) {
            throw new UnexpectedValueException('invalid symbol');
        }
        $this->symbol = $symbol;
    }

    public function evaluate(Lisphp_Scope $scope)
    {
        return $scope[$this];
    }

    public function __toString()
    {
        return $this->symbol;
    }
}
