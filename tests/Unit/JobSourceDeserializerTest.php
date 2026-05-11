<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\JobSourceDeserializer;
use SmartAssert\WorkerJobSource\Validator\ManifestContentValidator;
use SmartAssert\YamlFile\Collection\Deserializer as YamlFileCollectionDeserializer;
use SmartAssert\YamlFile\FileHashes\Deserializer as FileHashesDeserializer;
use Symfony\Component\Yaml\Parser;
use webignition\YamlDocumentSetParser\Parser as DocumentSetParser;

class JobSourceDeserializerTest extends TestCase
{
    private JobSourceDeserializer $jobSourceDeserializer;

    protected function setUp(): void
    {
        parent::setUp();

        $yamlParser = new Parser();

        $this->jobSourceDeserializer = new JobSourceDeserializer(
            new YamlFileCollectionDeserializer(
                new DocumentSetParser(),
                new FileHashesDeserializer($yamlParser),
            ),
            new JobSourceFactory(new ManifestContentValidator($yamlParser)),
        );
    }

    #[DataProvider('serializeDataProvider')]
    public function testDeserialize(string $serialized, \Exception $expected): void
    {
        $exception = null;

        try {
            $this->jobSourceDeserializer->deserialize($serialized);
        } catch (\Exception $exception) {
        }

        self::assertEquals($expected, $exception);
    }

    /**
     * @return array<mixed>
     */
    public static function serializeDataProvider(): array
    {
        return [
            'empty manifest' => [
                'serialized' => <<< 'EOD'
                    ---
                    328db4ecb7776156bd52599d25a93a1f:
                        - manifest.yaml
                    ...
                    ---
                    {  }
                    ...
                    EOD,
                'expected' => new InvalidManifestException(
                    '{  }',
                    'manifest.yaml represents an empty collection.',
                    InvalidManifestException::CODE_EMPTY_TEST_PATH_COLLECTION,
                ),
            ],
        ];
    }
}
