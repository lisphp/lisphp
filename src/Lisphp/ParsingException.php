<?php

class Lisphp_ParsingException extends Exception
{
    public $code, $offset, $lisphpFile;

    public function __construct($code, $offset, $file = '')
    {
        $this->code = $code;
        $this->offset = $offset;
        $this->lisphpFile = $file;
        $on = ($file ? "$file:" : '')
            . $this->getLisphpLine() . ':'
            . $this->getLisphpColumn();
        $this->message = "parsing error on $on";
    }

    public function getLisphpFile()
    {
        return $this->lisphpFile;
    }

    public function getLisphpLine()
    {
        if ($this->offset <= 0) return 1;

        return substr_count($this->code, "\n", 0, $this->offset) + 1;
    }

    public function getLisphpColumn()
    {
        $pos = strrpos(substr($this->code, 0, $this->offset), "\n");

        return $this->offset - ($pos === false ? -1 : $pos);
    }
}
