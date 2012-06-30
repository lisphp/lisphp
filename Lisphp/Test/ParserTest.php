<?php
require_once 'Lisphp/Program.php';
require_once 'Lisphp/Parser.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Literal.php';
require_once 'Lisphp/Test/TestCase.php';

class Lisphp_Test_ParserTest extends Lisphp_Test_TestCase {
    function assertForm($value, $offset, $expression) {
        $actual = Lisphp_Parser::parseForm($expression, $pos);
        $this->assertEquals($value, $actual);
        $this->assertEquals($offset, $pos);
    }

    function testParse() {
        $expected = array(
            new Lisphp_Literal('this is a docstring'),
            new Lisphp_List(array(Lisphp_Symbol::get('define'),
                                  new Lisphp_List(array(
                                      Lisphp_Symbol::get('add'),
                                      Lisphp_Symbol::get('a'),
                                      Lisphp_Symbol::get('b')
                                  )),
                                  new Lisphp_List(array(
                                      Lisphp_Symbol::get('+'),
                                      Lisphp_Symbol::get('a'),
                                      Lisphp_Symbol::get('b')
                                  )))),
            new Lisphp_List(array(Lisphp_Symbol::get('define'),
                                  new Lisphp_List(array(
                                      Lisphp_Symbol::get('sub'),
                                      Lisphp_Symbol::get('a'),
                                      Lisphp_Symbol::get('b')
                                  )),
                                  new Lisphp_List(array(
                                      Lisphp_Symbol::get('-'),
                                      Lisphp_Symbol::get('a'),
                                      Lisphp_Symbol::get('b')
                                  ))))
        );
        $program = '
            "this is a docstring"
            (define (add a b) (+ a b))
            (define (sub a b) (- a b))
        ';
        $this->assertEquals($expected, Lisphp_Parser::parse($program, true));
        $this->assertType('Lisphp_Program', Lisphp_Parser::parse($program));
        try {
            Lisphp_Parser::parse($code = '
                (correct form)
                (incorrect form}
                (correct form)
            ', true);
            $this->fail();
        } catch (Lisphp_ParsingException $e) {
            $this->assertEquals($code, $e->code);
            $this->assertEquals(63, $e->offset);
        }
    }

    function testParseForm_list() {
        $expected = new Lisphp_List(array(
            Lisphp_Symbol::get('define'),
            Lisphp_Symbol::get('add'),
            new Lisphp_List(array(
                Lisphp_Symbol::get('lambda'),
                new Lisphp_List(array(
                    Lisphp_Symbol::get('a'),
                    Lisphp_Symbol::get('b')
                )),
                new Lisphp_List(array(
                    Lisphp_Symbol::get('+'),
                    Lisphp_Symbol::get('a'),
                    Lisphp_Symbol::get('b')
                ))
            ))
        ));
        $this->assertForm($expected, 35,
                                '(define add {lambda [a b] (+ a b)})');
        try {
            Lisphp_Parser::parseForm('(abc d ])', $offset);
            $this->fails();
        } catch(Lisphp_ParsingException $e) {
            $this->assertEquals('(abc d ])', $e->code);
            $this->assertEquals(7, $e->offset);
        }
    }

    function testParseForm_quote() {
        $this->assertForm(new Lisphp_Quote(Lisphp_Symbol::get('abc')), 4,
                          ':abc');
        $this->assertForm(new Lisphp_Quote(new Lisphp_List(array(
                              Lisphp_Symbol::get('add'),
                              new Lisphp_Literal(2),
                              new Lisphp_Literal(3)
                          ))),
                          10,
                          ':(add 2 3)');
    }

    function testParseForm_integer() {
        $this->assertForm(new Lisphp_Literal(123), 3, '123');
        $this->assertForm(new Lisphp_Literal(123), 4, '+123 ');
        $this->assertForm(new Lisphp_Literal(-123), 4, '-123');
        $this->assertForm(new Lisphp_Literal(0xff), 4, '0xff');
        $this->assertForm(new Lisphp_Literal(0xff), 5, '+0XFF');
        $this->assertForm(new Lisphp_Literal(-0xff), 5, '-0xFf');
        $this->assertForm(new Lisphp_Literal(0765), 4, '0765');
        $this->assertForm(new Lisphp_Literal(0765), 5, '+0765');
        $this->assertForm(new Lisphp_Literal(-0765), 5, '-0765');
    }

    function testParseForm_real() {
        $this->assertForm(new Lisphp_Literal(1.234), 5, '1.234');
        $this->assertForm(new Lisphp_Literal(1.23), 5, '+1.23');
        $this->assertForm(new Lisphp_Literal(-1.23), 5, '-1.23');
        $this->assertForm(new Lisphp_Literal(.1234), 5, '.1234');
        $this->assertForm(new Lisphp_Literal(.123), 5, '+.123');
        $this->assertForm(new Lisphp_Literal(-.123), 5, '-.123');
        $this->assertForm(new Lisphp_Literal(1.2e3), 5, '1.2e3');
        $this->assertForm(new Lisphp_Literal(1.2e3), 6, '+1.2e3');
        $this->assertForm(new Lisphp_Literal(-1.2e3), 6, '-1.2e3');
    }

    function testParseForm_string() {
        $this->assertForm(new Lisphp_Literal("abcd efg \"q1\"\n\t'q2'"),
                                27,
                                '"abcd efg \\"q1\\"\n\\t\\\'q2\\\'"');
    }

    function testParseForm_symbol() {
        $this->assertForm(Lisphp_Symbol::get('abc'), 3, 'abc');
        $this->assertForm(Lisphp_Symbol::get('-abcd'), 5, '-abcd ');
        $this->assertForm(Lisphp_Symbol::get('-'), 1, '-');
        $this->assertForm(Lisphp_Symbol::get('+'), 1, '+');
    }
}

