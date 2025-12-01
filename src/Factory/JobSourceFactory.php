<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Factory;

use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\WorkerJobSource\Validator\ManifestContentValidator;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;

class JobSourceFactory
{
    public function __construct(
        private readonly ManifestContentValidator $manifestContentValidator,
    ) {}

    /**
     * @throws InvalidManifestException
     */
    public function createFromYamlFileCollection(ProviderInterface $provider): JobSource
    {
        $manifestYamlFile = null;
        $sources = [];
        foreach ($provider->getYamlFiles() as $yamlFile) {
            if (Manifest::FILENAME === (string) $yamlFile->name) {
                $manifestYamlFile = $yamlFile;
            } else {
                $sources[] = $yamlFile;
            }
        }

        $testPaths = $this->manifestContentValidator->validate($manifestYamlFile?->content ?? '');

        return new JobSource(new Manifest($testPaths), new ArrayCollection($sources));
    }
}
