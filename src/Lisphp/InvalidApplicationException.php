<?php

class Lisphp_InvalidApplicationException extends BadFunctionCallException {
    public $valueToApply;

    function __construct($valueToApply, Lisphp_List $list = null) {
        $this->valueToApply = $valueToApply;
        $this->list = $list;
        $type = is_object($this->valueToApply)
              ? get_class($this->valueToApply)
              : (is_null($this->valueToApply) ? 'nil'
                                              : gettype($this->valueToApply));
        $msg = "$type cannot be applied; see Lisphp_Applicable interface";
        if ($list) {
            $msg .= ': ' . $list->__toString();
        }
        parent::__construct($msg);
    }
}
