<?php
require_once 'PHPUnit/Framework.php';
require_once 'Lisphp/Quote.php';
require_once 'Lisphp/Scope.php';

class Lisphp_Test_QuoteTest extends PHPUnit_Framework_TestCase {
    function testEvaluate() {
        $quote = new Lisphp_Quote(new Lisphp_Symbol('abc'));
        $this->assertEquals(new Lisphp_Symbol('abc'),
                            $quote->evaluate(new Lisphp_Scope));
    }

    function testToString() {
        $quote = new Lisphp_Quote(new Lisphp_Symbol('abc'));
        $this->assertEquals(':abc', $quote->__toString());
        $quote = new Lisphp_Quote(new Lisphp_List(array(
            new Lisphp_Symbol('define'),
            new Lisphp_Symbol('pi'),
            new Lisphp_Literal(3.14)
        )));
        $this->assertEquals(':(define pi 3.14)', $quote->__toString());
    }
}

