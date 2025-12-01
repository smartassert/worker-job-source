<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Factory;

use SmartAssert\WorkerJobSource\Enum\ManifestValidityState;
use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;
use SmartAssert\YamlFile\YamlFile;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

class JobSourceFactory
{
    public function __construct(
        private readonly YamlDumper $yamlDumper,
        private readonly YamlParser $yamlParser,
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

        $manifest = $manifestYamlFile instanceof YamlFile ? $this->createManifest($manifestYamlFile) : null;
        if (null === $manifest) {
            throw InvalidManifestException::createForEmptyContent();
        }

        $validityState = $manifest->validate();

        if (ManifestValidityState::VALID !== $validityState) {
            if (ManifestValidityState::EMPTY === $validityState) {
                throw InvalidManifestException::createForEmptyContent();
            }

            throw InvalidManifestException::createForInvalidData(
                trim($this->yamlDumper->dump($manifest->testPaths, 1))
            );
        }

        return new JobSource($manifest, new ArrayCollection($sources));
    }

    /**
     * @throws InvalidManifestException
     */
    private function createManifest(YamlFile $yamlFile): Manifest
    {
        if ('' === trim($yamlFile->content)) {
            return new Manifest([]);
        }

        try {
            $data = $this->yamlParser->parse($yamlFile->content);
        } catch (ParseException $parseException) {
            throw InvalidManifestException::createForInvalidYaml($yamlFile->content, $parseException);
        }

        if (false === is_array($data)) {
            throw InvalidManifestException::createForInvalidData($yamlFile->content);
        }

        return new Manifest($data);
    }
}
