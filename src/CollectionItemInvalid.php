<?php

namespace Linio\Component\Input;

class CollectionItemInvalid extends Invalid
{
    public function __construct(public int $index, string $field, InputError|array|string $reason)
    {
        parent::__construct($field, $reason);
        $this->field = $field;
        $this->reason = $reason;
    }
}
