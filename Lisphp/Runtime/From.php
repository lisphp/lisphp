<?php
require_once 'Lisphp/Applicable.php';
require_once 'Lisphp/List.php';
require_once 'Lisphp/Symbol.php';
require_once 'Lisphp/Scope.php';
require_once 'Lisphp/Runtime/Use.php';

final class Lisphp_Runtime_From implements Lisphp_Applicable {
    function apply(Lisphp_Scope $scope, Lisphp_List $arguments) {
        $tmp = new Lisphp_Scope;
        $use = new Lisphp_Runtime_Use;
        $ns = (string) $arguments->car();
        $simpleNames = iterator_to_array($arguments[1]);
        foreach ($simpleNames as $name) {
            $names[] = Lisphp_Symbol::get("$ns/$name");
        }
        $retval = $use->apply($tmp, new Lisphp_List($names));
        foreach ($simpleNames as $i => $name) {
            $scope->let($name, $retval[$i]);
        }
        return $retval;
    }
}

