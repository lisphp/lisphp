<?php

final class Lisphp_Runtime_List_Cdr extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($list) = $arguments;
        if (is_array($list)) return array_slice($list, 1);
        if ($list instanceof Iterator || $list instanceof IteratorAggregate) {
            $it = $list instanceof Iterator ? $list : $list->getIterator();
            if (!$it->valid()) return;
            $result = array();
            for ($it->next(); $it->valid(); $it->next()) {
                $result[] = $it->current();
            }
            return new Lisphp_List($result);
        }
        throw new InvalidArgumentException('expected a list');
    }
}
