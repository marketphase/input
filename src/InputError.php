<?php

namespace Linio\Component\Input;

abstract class InputError
{
    public function __construct(
        public string $field,
        /** @psalm-var string|InputError|array<array-key,InputError> */
        protected string|InputError|array $reason
    ) {
    }

    public function getReason(): array|string
    {
        if (is_string($this->reason)) {
            return $this->reason;
        }
        if (is_a($this->reason, ScalarCollectionItemInvalid::class)) {
            return $this->reason->getReason();
        }
        if (is_a($this->reason, InputError::class)) {
            return [$this->field => $this->reason->getReason()];
        }
        $reasons = [];
        if ($this->containsScalarCollectionInputError($this->reason)) {
            $reasons[$this->field] = [];
            foreach ($this->reason as $singleReason) {
                assert($singleReason instanceof ScalarCollectionItemInvalid);
                $reasons[$this->field][$singleReason->index] = $singleReason->getReason();
            }
        } else {
            /** @var InputError $singleReason */
            foreach ($this->reason as $singleReason) {
                $reasons[$singleReason->field] = $singleReason->getReason();
            }
        }

        return $reasons;
    }

    private function containsScalarCollectionInputError(array $reasons): bool
    {
        foreach ($reasons as $reason) {
            if ($reason instanceof ScalarCollectionItemInvalid) {
                return true;
            }
        }
        return false;
    }
}
