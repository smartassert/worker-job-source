<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Model;

use SmartAssert\WorkerJobSource\Enum\ManifestValidityState;

class Manifest
{
    public const FILENAME = 'manifest.yaml';

    /**
     * @param array<int, non-empty-string> $testPaths
     */
    public function __construct(
        public readonly array $testPaths,
    ) {}

    public function contains(string $path): bool
    {
        return in_array($path, $this->testPaths);
    }

    public function validate(): ManifestValidityState
    {
        $isEmpty = true;

        foreach ($this->testPaths as $testPath) {
            if (!is_string($testPath)) {
                return ManifestValidityState::INVALID_CONTENT;
            }

            if ('' !== trim($testPath)) {
                $isEmpty = false;
            }
        }

        if ($isEmpty) {
            return ManifestValidityState::EMPTY;
        }

        return ManifestValidityState::VALID;
    }
}
