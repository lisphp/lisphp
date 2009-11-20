<?php
define('LISPHP_VERSION', '0.9.0');

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

require_once 'Lisphp/Program.php';
require_once 'Lisphp/Parser.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime.php';
require_once 'Lisphp/Environment.php';

