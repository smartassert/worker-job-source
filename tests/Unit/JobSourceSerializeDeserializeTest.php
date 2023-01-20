<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\JobSourceDeserializer;
use SmartAssert\WorkerJobSource\JobSourceSerializer;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\Deserializer as YamlFileCollectionDeserializer;
use SmartAssert\YamlFile\Collection\Serializer as YamlFileCollectionSerializer;
use SmartAssert\YamlFile\FileHashes\Deserializer as FileHashesDeserializer;
use SmartAssert\YamlFile\FileHashes\Serializer as FileHashesSerializer;
use SmartAssert\YamlFile\YamlFile;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Parser;
use webignition\YamlDocumentSetParser\Parser as DocumentSetParser;

class JobSourceSerializeDeserializeTest extends TestCase
{
    private JobSourceSerializer $jobSourceSerializer;
    private JobSourceDeserializer $jobSourceDeserializer;

    protected function setUp(): void
    {
        parent::setUp();

        $yamlDumper = new YamlDumper();
        $yamlParser = new Parser();

        $this->jobSourceSerializer = new JobSourceSerializer(
            new YamlFileCollectionSerializer(
                new FileHashesSerializer($yamlDumper)
            ),
            new YamlFileFactory($yamlDumper),
        );

        $this->jobSourceDeserializer = new JobSourceDeserializer(
            new YamlFileCollectionDeserializer(
                new DocumentSetParser(),
                new FileHashesDeserializer($yamlParser),
            ),
            new JobSourceFactory($yamlDumper, $yamlParser),
        );
    }

    /**
     * @dataProvider serializeDataProvider
     */
    public function testSerializeDeserialize(JobSource $jobSource): void
    {
        $serialized = $this->jobSourceSerializer->serialize($jobSource);

        self::assertIsString($serialized);
        self::assertNotSame('', trim($serialized));

        $deserialized = $this->jobSourceDeserializer->deserialize($serialized);

        self::assertEquals($jobSource, $deserialized);
    }

    /**
     * @return array<mixed>
     */
    public function serializeDataProvider(): array
    {
        return [
            'non-empty manifest, no sources' => [
                'jobSource' => new JobSource(
                    new Manifest([
                        'test1.yaml',
                    ]),
                    new ArrayCollection([]),
                ),
            ],
            'non-empty manifest, has sources' => [
                'jobSource' => new JobSource(
                    new Manifest([
                        'test1.yaml',
                        'test2.yaml',
                    ]),
                    new ArrayCollection([
                        YamlFile::create('test1.yaml', 'test 1 content'),
                        YamlFile::create('test2.yaml', 'test 2 content'),
                    ]),
                ),
            ],
        ];
    }
}
