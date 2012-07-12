<?php

final class Lisphp_Runtime_List_Car extends Lisphp_Runtime_BuiltinFunction
{
    protected function execute(array $arguments)
    {
        list($list) = $arguments;
        if ($list instanceof Iterator) {
            $list->rewind();
            $value = $list->valid() ? $list->current() : null;
        } elseif ($list instanceof IteratorAggregate) {
            $iter = $list->getIterator();
            $value = $iter->valid() ? $iter->current() : null;
        } elseif (is_array($list) || $list instanceof ArrayAccess) {
            $value = isset($list[0]) ? $list[0] : null;
        } else {
            throw new InvalidArgumentException('expected a list');
        }
        if (!is_null($value)) return $value;
        throw new UnexpectedValueException('list is empty');
    }
}
