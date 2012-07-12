<?php

class Lisphp_Test_FunctionalTest extends Lisphp_Test_TestCase {
    function testFromFile() {
        $testFiles = glob(dirname(__FILE__) . '/Functional/*.lisphp');

        foreach ($testFiles as $file) {
            $program = Lisphp_Program::load($file);
            $result = '';
            $scope = Lisphp_Environment::full();
            $scope['echo'] = new Lisphp_Runtime_PHPFunction('displayStrings');
            $program->execute($scope);
            $expected = file_get_contents(preg_replace('/\.lisphp$/', '.out', $file));

            $this->assertSame(trim($expected), trim($result));
        }
    }
}
