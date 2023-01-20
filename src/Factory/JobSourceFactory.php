<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Factory;

use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\Collection\MutableProviderInterface;
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
    ) {
    }

    /**
     * @param non-empty-string[] $manifestPaths
     *
     * @throws InvalidManifestException
     */
    public function createFromManifestPathsAndSources(array $manifestPaths, ProviderInterface $sources): JobSource
    {
        $manifest = new Manifest($manifestPaths);
        $this->validateManifest($manifest);

        return new JobSource($manifest, $sources);
    }

    /**
     * @throws InvalidManifestException
     */
    public function createFromYamlFileCollection(ProviderInterface&MutableProviderInterface $provider): JobSource
    {
        $manifestYamlFile = $provider->extract(Manifest::FILENAME);
        $manifest = $manifestYamlFile instanceof YamlFile ? $this->createManifest($manifestYamlFile) : null;
        $manifestTestPaths = $manifest?->testPaths ?? [];

        return $this->createFromManifestPathsAndSources($manifestTestPaths, $provider);
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

    /**
     * @throws InvalidManifestException
     */
    private function validateManifest(Manifest $manifest): void
    {
        if ($manifest->isEmpty()) {
            throw InvalidManifestException::createForEmptyContent();
        }

        if (!$manifest->containsOnlyStrings()) {
            $invalidContent = trim($this->yamlDumper->dump(
                $manifest->testPaths,
                1
            ));

            throw InvalidManifestException::createForInvalidData($invalidContent);
        }
    }
}
