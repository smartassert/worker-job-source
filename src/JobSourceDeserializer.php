<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource;

use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\YamlFile\Collection\Deserializer;
use SmartAssert\YamlFile\Exception\Collection\DeserializeException;

class JobSourceDeserializer
{
    public function __construct(
        private readonly Deserializer $yamlFileCollectionDeserializer,
        private readonly JobSourceFactory $jobSourceFactory,
    ) {}

    /**
     * @throws InvalidManifestException
     * @throws DeserializeException
     */
    public function deserialize(string $serialized): JobSource
    {
        $provider = $this->yamlFileCollectionDeserializer->deserialize($serialized);

        return $this->jobSourceFactory->createFromYamlFileCollection($provider);
    }
}
