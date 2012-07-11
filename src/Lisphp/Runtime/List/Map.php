<?php

final class Lisphp_Runtime_List_Map extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        if (!$function = array_shift($arguments)) {
            throw new InvalidArgumentException('missing function');
        } elseif (!isset($arguments[0])) {
            throw new InvalidArgumentException('least one list is required');
        }
        $map = array();
        foreach ($arguments as &$list) {
            if ($list instanceof IteratorAggregate) {
                $list = $list->getIterator();
            } elseif (is_array($list)) {
                $list = new ArrayIterator($list);
            } elseif (!($list instanceof Iterator)) {
                throw new InvalidArgumentException('expected list');
            }
        }
        $map = array();
        while (true) {
            $values = array();
            foreach ($arguments as $it) {
                if (!$it->valid()) break 2;
                $values[] = $it->current();
                $it->next();
            }
            $map[] = Lisphp_Runtime_Function::call($function, $values);
        }

        return new Lisphp_List($map);
    }
}
