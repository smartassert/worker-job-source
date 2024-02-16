<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use SmartAssert\WorkerJobSource\Factory\YamlFileFactory;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\YamlFile;
use Symfony\Component\Yaml\Dumper as YamlDumper;

class YamlFileFactoryTest extends TestCase
{
    private YamlFileFactory $yamlFileFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->yamlFileFactory = new YamlFileFactory(new YamlDumper());
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(Manifest $manifest, YamlFile $expected): void
    {
        self::assertEquals($expected, $this->yamlFileFactory->createFromManifest($manifest));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'manifest' => new Manifest([]),
                'expected' => YamlFile::create(Manifest::FILENAME, '{  }'),
            ],
            'single item' => [
                'manifest' => new Manifest([
                    'test1.yaml',
                ]),
                'expected' => YamlFile::create(Manifest::FILENAME, '- test1.yaml'),
            ],
            'multiple items' => [
                'manifest' => new Manifest([
                    'test1.yaml',
                    'test2.yaml',
                    'test3.yaml',
                ]),
                'expected' => YamlFile::create(
                    Manifest::FILENAME,
                    <<<'MANIFEST_CONTENT'
                - test1.yaml
                - test2.yaml
                - test3.yaml
                MANIFEST_CONTENT
                ),
            ],
        ];
    }
}
