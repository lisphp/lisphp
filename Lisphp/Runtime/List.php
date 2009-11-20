<?php
require_once 'Lisphp/Runtime/BuiltinFunction.php';
require_once 'Lisphp/List.php';

final class Lisphp_Runtime_List_Car extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($list) = $arguments;
        if ($list instanceof Iterator) {
            $list->rewind();
            $value = $list->valid() ? $list->current() : null;
        } else if ($list instanceof IteratorAggregate) {
            $iter = $list->getIterator();
            $value = $iter->valid() ? $iter->current() : null;
        } else if (is_array($list) || $list instanceof ArrayAccess) {
            $value = isset($list[0]) ? $list[0] : null;
        } else {
            throw new InvalidArgumentException('expected a list');
        }
        if (!is_null($value)) return $value;
        throw new UnexpectedValueException('list is empty');
    }
}

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

final class Lisphp_Runtime_List_At extends Lisphp_Runtime_BuiltinFunction {
    protected function execute(array $arguments) {
        list($list, $offset) = $arguments;
        if (isset($list[$offset])) return $list[$offset];
        $offset = var_export($offset, true);
        throw new OutOfRangeException("no index $offset of the list");
    }
}

