<?php

declare(strict_types=1);

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\CollectionItemInvalid;
use Linio\Component\Input\CollectionItemMissing;
use Linio\Component\Input\InputError;

class CollectionNode extends BaseNode
{
    public function getValue(string $field, $value)
    {
        $items = [];

        foreach ($value as $index => $collectionValue) {
            if ($collectionValue instanceof InputError) {
                $items[] = new CollectionItemInvalid($index, $field, $collectionValue);
                continue;
            }

            if ((is_array($collectionValue) && $this->containsError($collectionValue))) {
                $items[] = $collectionValue;
                continue;
            }

            if ($this->allowNull() && $value === null) {
                $items[] = null;
                continue;
            }

            $error = $this->checkConstraintsWithError($field, $collectionValue);
            if ($error !== null) {
                $items[] = $error;
                continue;
            }

            $items[] = $this->instantiator->instantiate($this->type, $collectionValue);
        }

        return $items;
    }

    public function walk($input)
    {
        $result = [];

        if (!$this->hasChildren()) {
            return $input;
        }

        foreach ($input as $index => $inputItem) {
            $itemResult = [];

            foreach ($this->getChildren() as $field => $config) {
                if (!array_key_exists($field, $inputItem)) {
                    if ($config->isRequired()) {
                        $itemResult[$field] = new CollectionItemMissing($index, $field, $config->getFieldMissingMessage());
                        continue;
                    }

                    if (!$config->hasDefault()) {
                        continue;
                    }

                    $inputItem[$field] = $config->getDefault();
                }
                $itemResult[$field] = $config->getValue($field, $config->walk($inputItem[$field]));
            }
            $result[] = $itemResult;
        }

        return $result;
    }
}
