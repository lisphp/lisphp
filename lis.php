<?php
require_once 'Lisphp.php';

define('LISPHP_COLUMN', 80);
define('LISPHP_REPL_PROMPT', '>>> ');
define('LISPHP_REPL_VALUE_PROMPT', '==> ');
define('LISPHP_REPL_EXCEPTION_PROMPT', '!!! ');

function Lisphp_usage() {
    static $commands = array(
        '-c <code>' => 'Evaluate the code.',
        '-h' => 'Print this help message.',
        '-s' => 'Safe sandbox mode.',
        '-v' => 'Print the Lisphp version number.'
    );
    $cmdlen = max(array_map('strlen', array_keys($commands)));
    $helplen = LISPHP_COLUMN - $cmdlen - 3;
    $usage = '';
    foreach ($commands as $cmd => $help) {
        preg_match_all("/.{0,{$helplen}}/", $help, $lines);
        $usage .= sprintf("  %-{$cmdlen}s %s", $cmd, join("\n", $lines[0]));
    }
    return $usage;
}

function Lisphp_printParsingError(Lisphp_ParsingException $e) {
    echo $e->getMessage(), "\n";
    $lines = explode("\n", $e->code);
    echo $lines[$e->getLisphpLine() - 1], "\n";
    echo str_repeat(' ', $e->getLisphpColumn() - 1), "^\n";
}

$options = getopt('hvsc:');
if (isset($options['h']) || isset($options['v'])) {
    echo 'Lisphp ' . LISPHP_VERSION . "\n";
    if (isset($options['v'])) {
        echo 'PHP-', PHP_VERSION, "\n", php_uname(), "\n";
    }
    if (isset($options['h'])) {
        echo "Usage: {$_SERVER['argv'][0]} [options] <file>\n\n";
        echo Lisphp_usage(), "\n";
    }
    exit;
}

$environment = isset($options['s'])
             ? Lisphp_Environment::sandbox()
             : Lisphp_Environment::full();

$scope = new Lisphp_Scope($environment);
$scope['echo'] = new Lisphp_Runtime_PHPFunction(create_function('', '
    $args = func_get_args();
    foreach ($args as $arg) echo $arg;
'));

class Lisphp_EnterREPL extends Exception {}

try {
    $file = end($_SERVER['argv']);
    if (isset($options['c'])) {
        $program = new Lisphp_Program($options['c']);
    } else if (count($_SERVER['argv']) > 1 && $file != '-s') {
        $program = Lisphp_Program::load($file);
    } else {
        throw new Lisphp_EnterREPL;
    }
    $program->execute($scope);
} catch (Lisphp_ParsingException $e) {
    Lisphp_printParsingError($e);
} catch (Lisphp_EnterREPL $e) {
    $scope['exit'] = new Lisphp_Runtime_PHPFunction(
        create_function('$status = null', '
            if (is_null($status)) die;
            else die($status);
        ')
    );
    if (extension_loaded('readline')) {
        readline_completion_function(create_function('$line', '
            global $scope;
            $symbols = array();
            foreach ($scope->listSymbols() as $symbol) {
                if ($line != "" && strpos($symbol, $line) !== 0) continue;
                $symbols[] = $symbol;
            }
            if (!isset($symbols[0])) {
                $symbols[] = $line;
            }
            return $symbols;
        '));
        $readline = 'readline';
        $add_history = 'readline_add_history';
        $exit = false;
    } else {
        $readline = create_function('$prompt', '
            echo $prompt;
            return fread(STDIN, 8192);
        ');
        $add_history = create_function('', '');
        $exit = '';
    }
    while (true) {
        $code = $readline(LISPHP_REPL_PROMPT);
        if ($code === $exit) die("\n");
        else if (trim($code) == '') continue;
        try {
            $form = Lisphp_Parser::parseForm($code, $_);
            echo LISPHP_REPL_VALUE_PROMPT;
            var_export($form->evaluate($scope));
            echo "\n";
        } catch (Lisphp_ParsingException $e) {
            Lisphp_printParsingError($e);
        } catch (Exception $e) {
            echo LISPHP_REPL_EXCEPTION_PROMPT, $e->getMessage(), "\n",
                 preg_replace('/^|\n/', '\\0    ', $e->getTraceAsString()),
                 "\n";
        }
        $add_history($code);
    }
}

