<?php

namespace Linio\Component\Input;

trait ChecksForErrors
{
    protected function containsError(array $values): bool
    {
        foreach ($values as $value) {
            if ($value instanceof InputError) {
                return true;
            }
        }
        return false;
    }
}
