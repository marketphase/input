<?php

namespace Linio\Component\Input;

class ScalarCollectionItemInvalid extends Invalid
{
    public function __construct(public int $index, string $field, InputError|array|string $reason)
    {
        parent::__construct($field, $reason);
    }
}
