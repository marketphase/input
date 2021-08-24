<?php

declare(strict_types=1);

namespace Linio\Component\Input\Node\Forgiving;

//TODO: update this class to actually be forgiving
class CollectionNode extends BaseNode
{
    public function __construct(string $fieldMissingMessage = '')
    {
        throw new \RuntimeException("This class needs to be adapted before it can be constructed");
    }

    public function getValue(string $field, $value)
    {
        //TODO: implement this method
    }

    public function walk($input)
    {
        //TODO: implement this method
    }
}
