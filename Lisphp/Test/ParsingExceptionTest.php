<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Parser.php';

class Lisphp_Test_ParsingExceptionTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->exception = new Lisphp_ParsingException('
            (use substr)
            (echo (substr "test" 1 2)}
            (echo "yes")
        ', 63, 'test.lisphp');
    }

    function testLine() {
        $this->assertEquals(3, $this->exception->getLisphpLine());
        $e = new Lisphp_ParsingException('{', 0);
        $this->assertEquals(1, $e->getLisphpLine());
    }

    function testColumn() {
        $this->assertEquals(38, $this->exception->getLisphpColumn());
        $e = new Lisphp_ParsingException('{', 0);
        $this->assertEquals(1, $e->getLisphpColumn());
    }

    function testFile() {
        $this->assertEquals('test.lisphp', $this->exception->getLisphpFile());
        $e = new Lisphp_ParsingException('(echo 1', 7);
        $this->assertEquals('', $e->getLisphpFile());
    }
}
