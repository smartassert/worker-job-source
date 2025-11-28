<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Tests\Unit\Factory;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use SmartAssert\WorkerJobSource\Factory\JobSourceFactory;
use SmartAssert\WorkerJobSource\Model\JobSource;
use SmartAssert\WorkerJobSource\Model\Manifest;
use SmartAssert\YamlFile\Collection\ArrayCollection;
use SmartAssert\YamlFile\Collection\ProviderInterface;
use SmartAssert\YamlFile\YamlFile;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use Symfony\Component\Yaml\Parser as YamlParser;

class JobSourceFactoryTest extends TestCase
{
    private JobSourceFactory $jobSourceFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobSourceFactory = new JobSourceFactory(new YamlDumper(), new YamlParser());
    }

    public function testCreateFromManifestPathsAndSourcesThrowsExceptionForEmptyContent(): void
    {
        self::expectExceptionObject(InvalidManifestException::createForEmptyContent());

        $this->jobSourceFactory->createFromManifestPathsAndSources([], new ArrayCollection([]));
    }

    public function testCreateFromManifestPathsAndSourcesThrowsExceptionForInvalidContent(): void
    {
        try {
            $this->jobSourceFactory->createFromManifestPathsAndSources(
                [
                    'valid string 1',
                    1,
                    'valid string 2',
                ],
                new ArrayCollection([])
            );
        } catch (InvalidManifestException $e) {
            self::assertSame(InvalidManifestException::CODE_INVALID_DATA, $e->getCode());
            self::assertSame(
                <<<'EXPECTED'
                - 'valid string 1'
                - 1
                - 'valid string 2'
                EXPECTED,
                $e->content
            );
        }
    }

    /**
     * @param non-empty-string[] $manifestPaths
     */
    #[DataProvider('createFromManifestPathsAndSourcesSuccessDataProvider')]
    public function testCreateFromManifestPathsAndSourcesSuccess(
        array $manifestPaths,
        ProviderInterface $sources,
        JobSource $expected
    ): void {
        self::assertEquals(
            $expected,
            $this->jobSourceFactory->createFromManifestPathsAndSources($manifestPaths, $sources)
        );
    }

    /**
     * @return array<mixed>
     */
    public static function createFromManifestPathsAndSourcesSuccessDataProvider(): array
    {
        return [
            'single-item manifest, no sources' => [
                'manifestPaths' => [
                    'Test/test1.yml',
                ],
                'sources' => new ArrayCollection([]),
                'expected' => new JobSource(
                    new Manifest(['Test/test1.yml']),
                    new ArrayCollection([])
                ),
            ],
            'multiple-item manifest, no sources' => [
                'manifestPaths' => [
                    'Test/test1.yml',
                    'Test/test2.yml',
                    'Test/test3.yml',
                ],
                'sources' => new ArrayCollection([]),
                'expected' => new JobSource(
                    new Manifest(['Test/test1.yml', 'Test/test2.yml', 'Test/test3.yml']),
                    new ArrayCollection([])
                ),
            ],
            'multiple-item manifest, has sources' => [
                'manifestPaths' => [
                    'Test/test1.yml',
                    'Test/test2.yml',
                ],
                'sources' => new ArrayCollection([
                    YamlFile::create('Test/test1.yml', 'test 1 content'),
                    YamlFile::create('Test/test2.yml', 'test 2 content'),
                    YamlFile::create('Page/page.yml', 'page content'),
                ]),
                'expected' => new JobSource(
                    new Manifest(['Test/test1.yml', 'Test/test2.yml']),
                    new ArrayCollection([
                        YamlFile::create('Test/test1.yml', 'test 1 content'),
                        YamlFile::create('Test/test2.yml', 'test 2 content'),
                        YamlFile::create('Page/page.yml', 'page content'),
                    ])
                ),
            ],
        ];
    }

    public function testCreateFromYamlFileCollectionThrowsExceptionForMissingManifest(): void
    {
        try {
            $this->jobSourceFactory->createFromYamlFileCollection(new ArrayCollection([]));
        } catch (InvalidManifestException $e) {
            self::assertSame(InvalidManifestException::CODE_EMPTY, $e->getCode());
        }
    }

    #[DataProvider('createFromYamlFileCollectionThrowsExceptionForInvalidManifestDataProvider')]
    public function testCreateFromYamlFileCollectionThrowsExceptionForInvalidManifest(
        string $manifestContent,
        int $expectedCode,
    ): void {
        $provider = new ArrayCollection([YamlFile::create('manifest.yaml', $manifestContent)]);

        try {
            $this->jobSourceFactory->createFromYamlFileCollection($provider);
        } catch (InvalidManifestException $e) {
            self::assertSame($expectedCode, $e->getCode());
            self::assertSame($manifestContent, $e->content);
        }
    }

    /**
     * @return array<mixed>
     */
    public static function createFromYamlFileCollectionThrowsExceptionForInvalidManifestDataProvider(): array
    {
        return [
            'empty' => [
                'manifestContent' => '',
                'expectedCode' => InvalidManifestException::CODE_EMPTY,
            ],
            'invalid yaml' => [
                'manifestContent' => ' - ' . "\n" . '1',
                'expectedCode' => InvalidManifestException::CODE_INVALID_YAML,
            ],
            'data is not an array' => [
                'manifestContent' => 'not an array',
                'expectedCode' => InvalidManifestException::CODE_INVALID_DATA,
            ],
            'data is not an array of only strings' => [
                'manifestContent' => <<<'MANIFEST_CONTENT'
                                    - 'valid string 1'
                                    - 1
                                    - 'valid string 2'
                                    MANIFEST_CONTENT,
                'expectedCode' => InvalidManifestException::CODE_INVALID_DATA,
            ],
        ];
    }

    #[DataProvider('createFromYamlFileCollectionSuccessDataProvider')]
    public function testCreateFromYamlFileCollectionSuccess(ProviderInterface $provider, JobSource $expected): void
    {
        self::assertEquals($expected, $this->jobSourceFactory->createFromYamlFileCollection($provider));
    }

    /**
     * @return array<mixed>
     */
    public static function createFromYamlFileCollectionSuccessDataProvider(): array
    {
        return [
            'single-item manifest, no sources' => [
                'provider' => new ArrayCollection([
                    YamlFile::create('manifest.yaml', '- Test/test1.yml'),
                ]),
                'expected' => new JobSource(
                    new Manifest(['Test/test1.yml']),
                    new ArrayCollection([])
                ),
            ],
            'multiple-item manifest, no sources' => [
                'provider' => new ArrayCollection([
                    YamlFile::create('manifest.yaml', <<<'MANIFEST_CONTENT'
                    - Test/test1.yml
                    - Test/test2.yml
                    - Test/test3.yml
                    MANIFEST_CONTENT),
                ]),
                'expected' => new JobSource(
                    new Manifest(['Test/test1.yml', 'Test/test2.yml', 'Test/test3.yml']),
                    new ArrayCollection([])
                ),
            ],
            'multiple-item manifest, has sources' => [
                'provider' => new ArrayCollection([
                    YamlFile::create('manifest.yaml', <<<'MANIFEST_CONTENT'
                    - Test/test1.yml
                    - Test/test2.yml
                    MANIFEST_CONTENT),
                    YamlFile::create('Test/test1.yml', 'test 1 content'),
                    YamlFile::create('Test/test2.yml', 'test 2 content'),
                    YamlFile::create('Page/page.yml', 'page content'),
                ]),
                'expected' => new JobSource(
                    new Manifest(['Test/test1.yml', 'Test/test2.yml']),
                    new ArrayCollection([
                        YamlFile::create('Test/test1.yml', 'test 1 content'),
                        YamlFile::create('Test/test2.yml', 'test 2 content'),
                        YamlFile::create('Page/page.yml', 'page content'),
                    ])
                ),
            ],
        ];
    }
}
