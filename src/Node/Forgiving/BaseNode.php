<?php

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\ChecksForErrors;
use Linio\Component\Input\InputError;
use Linio\Component\Input\InputHandler;
use Linio\Component\Input\Invalid;
use Linio\Component\Input\Missing;

class BaseNode extends \Linio\Component\Input\Node\BaseNode
{
    use ChecksForErrors;

    public function __construct(protected string $fieldMissingMessage = '')
    {
    }

    public function add(string $key, string $type, array $options = [], InputHandler $handler = null): BaseNode
    {
        $node = parent::add($key, $type, $options, $handler);
        assert($node instanceof BaseNode);
        return $node;
    }

    public function walk($input)
    {
        if (!is_array($input)) {
            return $input;
        }

        if (!$this->hasChildren()) {
            return $input;
        }

        $result = [];

        /**
         * @var string $field
         * @var BaseNode $config
         */
        foreach ($this->getChildren() as $field => $config) {
            if (!array_key_exists($field, $input)) {
                if ($config->isRequired()) {
                    $result[$field] = new Missing($field, $config->getFieldMissingMessage());
                }

                if (!$config->hasDefault()) {
                    continue;
                }

                $input[$field] = $config->getDefault();
            }

            $childValue = $config->walk($input[$field]);
            if ($childValue instanceof InputError || (is_array($childValue) && $this->containsError($childValue))) {
                $result[$field] = $childValue;
            } else {
                $result[$field] = $config->getValue($field, $childValue);
            }
        }

        return $result;
    }

    public function getValue(string $field, $value)
    {
        if ($value instanceof InputError) {
            return new Invalid($field, $value);
        }

        if ((is_array($value) && $this->containsError($value))) {
            return $value;
        }

        if ($this->allowNull() && $value === null) {
            return null;
        }

        $error = $this->checkConstraintsWithError($field, $value);
        if ($error !== null) {
            return $error;
        }
        if ($this->transformer) {
            return $this->transformer->transform($value);
        }

        return $value;
    }

    protected function checkConstraintsWithError(string $field, $value): ?Invalid
    {
        foreach ($this->constraints as $constraint) {
            if (!$constraint->validate($value) && ($this->isRequired() || $this->checkIfFieldValueIsSpecified($value))) {
                return new Invalid($field, $constraint->getErrorMessage($field));
            }
        }
        return null;
    }

    private function checkIfFieldValueIsSpecified($value): bool
    {
        return $this->type === 'string' || $this->type === 'array' ? !empty($value) : $value !== null;
    }

    public function getFieldMissingMessage(): string
    {
        return $this->fieldMissingMessage;
    }

    public function setFieldMissingMessage(string $fieldMissingMessage): self
    {
        $this->fieldMissingMessage = $fieldMissingMessage;
        return $this;
    }
}
