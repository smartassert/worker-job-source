<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Model;

use SmartAssert\WorkerJobSource\Enum\ManifestValidityState;

class ManifestValidity
{
    public function __construct(
        public readonly ManifestValidityState $state,
        public readonly ?\Throwable $throwable = null,
    ) {}

    public function isValid(): bool
    {
        return ManifestValidityState::VALID === $this->state;
    }
}
