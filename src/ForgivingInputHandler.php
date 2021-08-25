<?php

namespace Linio\Component\Input;

use Linio\Component\Input\Node\Forgiving\BaseNode;
use Linio\Component\Input\Node\Forgiving\TypeHandler as ForgivingTypeHandler;

abstract class ForgivingInputHandler extends InputHandler
{
    use ChecksForErrors;

    /** @var ForgivingTypeHandler */
    protected $typeHandler;

    public function __construct(ForgivingTypeHandler $typeHandler = null)
    {
        $typeHandler ??= new ForgivingTypeHandler();
        parent::__construct($typeHandler);
        $this->root = new BaseNode();
        $this->root->setTypeHandler($this->typeHandler);
    }

    public function add(string $key, string $type, array $options = [], InputHandler $handler = null): BaseNode
    {
        return $this->root->add($key, $type, $options, $handler);
    }

    /**
     * @var BaseNode
     */
    protected $root;

    protected ?bool $isValid = null;

    public function hasData($index): bool
    {
        return !$this->hasErrorFor($index) && parent::hasData($index);
    }

    public function bind(array $input): void
    {
        $this->define();

        $this->output = $this->root->getValue('root', $this->root->walk($input));
        $this->isValid = true;
        foreach ($this->output as $key => $value) {
            if ($this->hasErrorFor($key)) {
                $this->isValid = false;
            }
        }
    }

    public function isValid(): bool
    {
        if ($this->isValid() === null) {
            throw new \RuntimeException("Cannot determine the validity of an unbound input handler");
        }
        return $this->isValid;
    }

    public function hasErrorFor($index): bool
    {
        if (!is_array($this->output[$index])) {
            return $this->output[$index] instanceof InputError;
        }
        return $this->hasErrorForRec($this->output[$index]);
    }

    private function hasErrorForRec($output): bool
    {
        if (!is_array($output)) {
            return $output instanceof InputError;
        }

        foreach ($output as $child) {
            if ($this->hasErrorForRec($child)) {
                return true;
            }
        }
        return false;
    }

    public function getErrorFor(string $index): ?InputError
    {
        if ($this->hasErrorFor($index)) {
            return $this->findError($index, $this->output);
        }
        return null;
    }

    private function findError(string $index, $output): ?InputError
    {
        if (is_a($output, InputError::class)) {
            return $output;
        }
        if (!is_array($output)) {
            return null;
        }
        if (is_a($output[$index], InputError::class)) {
            return $output[$index];
        }
        if (is_array($output[$index]) && $this->containsError($output)) {
            return new Invalid($index, array_filter($output, fn ($item) => is_a($item, InputError::class)));
        }
        $errors = [];
        if (is_array($output[$index])) {
            foreach ($output[$index] as $childIndex => $childOutput) {
                $error = $this->findErrorRec($childIndex, $childOutput);
                if ($error !== null) {
                    $errors[] = $error;
                }
            }
        }
        if (!empty($errors)) {
            return new Invalid($index, $errors);
        }
        return null;
    }

    private function findErrorRec($index, $output): ?InputError
    {
        if (is_array($output)) {
            $errors = [];
            foreach ($output as $key => $value) {
                $error = $this->findError($key, $value);
                if ($error !== null) {
                    $errors[] = $error;
                }
            }
            if (!empty($errors)) {
                return new Invalid($index, $errors);
            }
        }
        if (is_a($output, InputError::class)) {
            return $output;
        }
        return null;
    }
}
