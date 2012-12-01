<?php

class Lisphp_ParsingExceptionTest extends Lisphp_TestCase
{
    public function setUp()
    {
        $this->exception = new Lisphp_ParsingException('
            (use substr)
            (echo (substr "test" 1 2)}
            (echo "yes")
        ', 63, 'test.lisphp');
    }

    public function testLine()
    {
        $this->assertEquals(3, $this->exception->getLisphpLine());
        $e = new Lisphp_ParsingException('{', 0);
        $this->assertEquals(1, $e->getLisphpLine());
    }

    public function testColumn()
    {
        $this->assertEquals(38, $this->exception->getLisphpColumn());
        $e = new Lisphp_ParsingException('{', 0);
        $this->assertEquals(1, $e->getLisphpColumn());
    }

    public function testFile()
    {
        $this->assertEquals('test.lisphp', $this->exception->getLisphpFile());
        $e = new Lisphp_ParsingException('(echo 1', 7);
        $this->assertEquals('', $e->getLisphpFile());
    }
}
