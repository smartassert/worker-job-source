<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Model;

class Manifest
{
    public const FILENAME = 'manifest.yaml';

    /**
     * @param array<int, non-empty-string> $testPaths
     */
    public function __construct(
        public readonly array $testPaths,
    ) {
    }

    public function contains(string $path): bool
    {
        return in_array($path, $this->testPaths);
    }

    public function isEmpty(): bool
    {
        $filteredPaths = [];

        foreach ($this->testPaths as $testPath) {
            if (is_string($testPath)) {
                $filteredPath = trim($testPath);

                if ('' !== $filteredPath) {
                    $filteredPaths[] = $filteredPath;
                }
            }
        }

        return [] === $filteredPaths;
    }

    public function containsOnlyStrings(): bool
    {
        foreach ($this->testPaths as $testPath) {
            if (!is_string($testPath)) {
                return false;
            }
        }

        return true;
    }
}
