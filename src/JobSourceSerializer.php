<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource;

use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\Serializer;

class JobSourceSerializer
{
    public function __construct(
        private readonly Serializer $yamlFileCollectionSerializer,
        private readonly YamlFileFactory $yamlFileFactory,
    ) {
    }

    public function serialize(JobSource $jobSource): string
    {
        $yamlFiles = [];
        foreach ($jobSource->sources->getYamlFiles() as $yamlFile) {
            $yamlFiles[] = $yamlFile;
        }

        return $this->yamlFileCollectionSerializer->serialize(new ArrayCollection(array_merge(
            [
                $this->yamlFileFactory->createFromManifest($jobSource->manifest)
            ],
            $yamlFiles,
        )));
    }
}
