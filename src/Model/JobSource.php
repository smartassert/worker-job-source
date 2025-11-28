<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Model;

use SmartAssert\YamlFile\Collection\ProviderInterface;

class JobSource
{
    public function __construct(
        public readonly Manifest $manifest,
        public readonly ProviderInterface $sources,
    ) {}
}
