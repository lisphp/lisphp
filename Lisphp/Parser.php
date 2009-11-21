<?php
require_once 'Lisphp/List.php';
require_once 'Lisphp/Quote.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Literal.php';
require_once 'Lisphp/Program.php';

final class Lisphp_Parser {
    const PARENTHESES = '(){}[]';
    const QUOTE_PREFIX = ':';
    const WHITESPACES = " \t\n\r\f\v\0";
    const REAL_PATTERN = '{^
        [+-]? ((\d+ | (\d* \. \d+ | \d+ \. \d*)) e [+-]? \d+
              | \d* \. \d+ | \d+ \. \d*)
    }ix';
    const INTEGER_PATTERN = '/^([+-]?)(0x([0-9a-f]+)|0([0-7]+)|[1-9]\d*|0)/i';
    const STRING_PATTERN = '/^"([^"\\\\]|\\\\.)*"|\'([^\'\\\\]|\\\\.)\'/';
    const STRING_ESCAPE_PATTERN = '/\\\\(([0-7]{1,3})|x([0-9A-Fa-f]{1,2})|.)/';
    const SYMBOL_PATTERN = '{^
        [^ \s \d () {} \[\] : +-] [^\s () {} \[\] :]*
    |   [+-] ([^ \s \d () {} \[\] :] [^ \s () {} \[\]]*)?
    }x';

    static function parse($program, $asArray = false) {
        if (!$asArray) return new Lisphp_Program($program);
        $i = 0;
        $len = strlen($program);
        $forms = array();
        while ($i < $len) {
            if (strpos(self::WHITESPACES, $program[$i]) === false) {
                try {
                    $forms[] = self::parseForm(substr($program, $i), $offset);
                } catch (Lisphp_ParsingException $e) {
                    throw new Lisphp_ParsingException($program,
                                                      $e->offset + $i);
                }
                $i += $offset;
            } else {
                ++$i;
            }
        }
        return $forms;
    }

    static function parseForm($form, &$offset) {
        static $parentheses = null;
        if (is_null($parentheses)) {
            $_parentheses = self::PARENTHESES;
            $parentheses = array();
            for ($i = 0, $len = strlen($_parentheses); $i < $len; $i += 2) {
                $parentheses[$_parentheses[$i]] = $_parentheses[$i + 1];
            }
            unset($_parentheses);
        }
        if (isset($form[0], $parentheses[$form[0]])) {
            $end = $parentheses[$form[0]];
            $values = array();
            $i = 1;
            $len = strlen($form);
            while ($i < $len && $form[$i] != $end) {
                if (strpos(self::WHITESPACES, $form[$i]) !== false) {
                    ++$i;
                    continue;
                }
                try {
                    $values[] = self::parseForm(substr($form, $i), $_offset);
                    $i += $_offset;
                } catch (Lisphp_ParsingException $e) {
                    throw new Lisphp_ParsingException($form, $i + $e->offset);
                }
            }
            if (isset($form[$i]) && $form[$i] == $end) {
                $offset = $i + 1;
                return new Lisphp_List($values);
            }
            throw new Lisphp_ParsingException($form, $i);
        } else if (isset($form[0]) && $form[0] == self::QUOTE_PREFIX) {
            $parsed = self::parseForm(substr($form, 1), $_offset);
            $offset = $_offset + 1;
            return new Lisphp_Quote($parsed);
        } else if (preg_match(self::REAL_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);
            return new Lisphp_Literal((float) $matches[0]);
        } else if (preg_match(self::INTEGER_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);
            $sign = $matches[1] == '-' ? -1 : 1;
            $value = !empty($matches[3]) ? hexdec($matches[3])
                   : (!empty($matches[4]) ? octdec($matches[4]) : $matches[2]);
            return new Lisphp_Literal($sign * $value);
        } else if (preg_match(self::STRING_PATTERN, $form, $matches)) {
            list($parsed) = $matches;
            $offset = strlen($parsed);
            return new Lisphp_Literal(
                preg_replace_callback(self::STRING_ESCAPE_PATTERN,
                                      array(__CLASS__, '_unescapeString'),
                                      substr($parsed, 1, -1))
            );
        } else if (preg_match(self::SYMBOL_PATTERN, $form, $matches)) {
            $offset = strlen($matches[0]);
            return Lisphp_Symbol::get($matches[0]);
        } else {
            throw new Lisphp_ParsingException($form, 0);
        }
    }

    protected static function _unescapeString($matches) {
        static $map = array('n' => "\n", 'r' => "\r", 't' => "\t", 'v' => "\v",
                            'f' => "\f");
        if (!empty($matches[2])) return chr(octdec($matches[2]));
        else if (!empty($matches[3])) return chr(hexdec($matches[3]));
        else if (isset($map[$matches[1]])) return $map[$matches[1]];
        else return $matches[1];
    }
}

class Lisphp_ParsingException extends Exception {
    public $code, $offset, $lisphpFile;

    function __construct($code, $offset, $file = '') {
        $this->code = $code;
        $this->offset = $offset;
        $this->lisphpFile = $file;
        $on = ($file ? "$file:" : '')
            . $this->getLisphpLine() . ':'
            . $this->getLisphpColumn();
        $this->message = "parsing error on $on";
    }

    function getLisphpFile() {
        return $this->lisphpFile;
    }

    function getLisphpLine() {
        if ($this->offset <= 0) return 1;
        return substr_count($this->code, "\n", 0, $this->offset) + 1;
    }

    function getLisphpColumn() {
        $pos = strrpos(substr($this->code, 0, $this->offset), "\n");
        return $this->offset - ($pos === false ? -1 : $pos);
    }
}

