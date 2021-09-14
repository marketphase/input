<?php

namespace Linio\Component\Input;

class CollectionItemMissing extends Missing
{
    public function __construct(public int|string $index, string $field, InputError|array|string $reason)
    {
        parent::__construct($field, $reason);
        $this->field = $field;
//        $this->reason = $reason;
    }
}
