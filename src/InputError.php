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
        if (is_a($this->reason, InputError::class)) {
            return [$this->field => $this->reason->getReason()];
        }
        $reasons = [];
        /** @var InputError $singleReason */
        foreach ($this->reason as $singleReason) {
            $reasons[$singleReason->field] = $singleReason->getReason();
        }
        return $reasons;
    }
}
