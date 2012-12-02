<?php

final class Lisphp_Runtime_Use implements Lisphp_Applicable
{
    public function apply(Lisphp_Scope $scope, Lisphp_List $arguments)
    {
        $values = array();
        foreach ($arguments as $name) {
            foreach ($this->dispatch($name) as $name => $value) {
                $scope->let($name, $value);
            }
            $values[] = $value;
        }

        return new Lisphp_List($values);
    }

    public function dispatch(Lisphp_Form $name)
    {
        if ($name instanceof Lisphp_Symbol) {
            $phpname = $name = $name->symbol;
        } else {
            $phpname = $name[0]->symbol;
            $name = $name[1]->symbol;
        }
        $phpname = str_replace('-', '_', $phpname);
        try {
            if (preg_match('|^<(.+?)>$|', $phpname, $matches)) {
                $phpname = substr($phpname, 1, -1);
                $class = new Lisphp_Runtime_PHPClass($phpname);
                foreach ($class->getStaticMethods() as $methodName => $method) {
                    $objs["$name/$methodName"] = $method;
                }
                $objs[$name] = $class;

                return $objs;
            }
            if (preg_match('|^\+(.+?)\+$|', $phpname, $matches)) {
                $phpname = substr($phpname, 1, -1);
                $objs[$name] = constant($phpname);

                return $objs;
            }

            return array($name => new Lisphp_Runtime_PHPFunction($phpname));
        } catch (UnexpectedValueException $e) {
            throw new InvalidArgumentException($e);
        }
    }
}
