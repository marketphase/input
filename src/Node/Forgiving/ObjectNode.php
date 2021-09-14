<?php

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\InputError;

class ObjectNode extends BaseNode
{
    protected function containsError(array $values): bool
    {
        foreach ($values as $value) {
            if ($value instanceof InputError) {
                return true;
            }
            if (is_array($value)) {
                return $this->containsError($value);
            }
        }
        return false;
    }

    public function getValue(string $field, $value)
    {
        $error = $this->checkConstraintsWithError($field, $value);
        if ($error instanceof InputError) {
            return $error;
        }
        if (is_array($value) && $this->containsError($value)) {
            return $value;
        }

        return $this->instantiator->instantiate(
            $this->type,
            is_array($value) ? $value : [$value]
        );
    }
}
