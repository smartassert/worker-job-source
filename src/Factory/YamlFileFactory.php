<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Factory;

use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\YamlFile;
use Symfony\Component\Yaml\Dumper as YamlDumper;

class YamlFileFactory
{
    public function __construct(
        private readonly YamlDumper $yamlDumper,
    ) {}

    public function createFromManifest(Manifest $manifest): YamlFile
    {
        return YamlFile::create(Manifest::FILENAME, trim($this->yamlDumper->dump($manifest->testPaths, 1)));
    }
}
