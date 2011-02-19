<?php
require dirname(__FILE__) . '/Lisphp.php';

$options = getopt('v', array('verbose'));

function displayStrings() {
    global $result;
    $args = func_get_args();
    $result .= join('', array_map('strval', $args));
}

$testFiles = glob(dirname(__FILE__) . '/tests/*.lisphp');
$fails = array();

foreach ($testFiles as $file) {
    $program = Lisphp_Program::load($file);
    $result = '';
    $scope = Lisphp_Environment::full();
    $scope['echo'] = new Lisphp_Runtime_PHPFunction('displayStrings');
    $program->execute($scope);
    $expected = file_get_contents(preg_replace('/\.lisphp$/', '.out', $file));
    if (trim($result) == trim($expected)) {
        echo '.';
    } else {
        echo 'F';
        $fails[$file] = $result;
    }
}

if ($fails) {
    echo "\nFailed: ";
} else {
    echo "\nOK ";
}
echo '(', count($testFiles), ' tests';
if ($fails) {
    echo ', ', count($fails), ' failed';
}
echo ")\n";

if ($fails) {
    if (isset($options['verbose']) || isset($options['v'])) {
      $br = str_repeat('-', 80);
      foreach ($fails as $file => $actual) {
        echo "$br\n$file\n$br\n$actual\n";
      }
      echo "$br\n";
    } else {
      $files = array_map('basename', array_keys($fails));
      echo "Failed tests: ", join(', ', $files), "\n";
    }
}
