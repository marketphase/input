<?php

declare(strict_types=1);

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\Constraint\Type;
use Linio\Component\Input\ScalarCollectionItemInvalid;

class ScalarCollectionNode extends BaseNode
{
    public function __construct(public string $itemInvalidMessage = 'This item is invalid')
    {
        parent::__construct();
        $this->addConstraint(new Type('array'));
    }

    public function getValue(string $field, $value)
    {
        $this->checkConstraints($field, $value);

        $values = [];
        foreach ($value as $index => $scalarValue) {
            $values[] = call_user_func('is_' . $this->type, $scalarValue)
                ? $scalarValue
                : new ScalarCollectionItemInvalid($index, $field, $this->itemInvalidMessage);
        }

        return $values;
    }
}
