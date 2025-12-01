<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Validator;

use SmartAssert\WorkerJobSource\Exception\InvalidManifestException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

readonly class ManifestContentValidator
{
    public function __construct(
        private YamlParser $yamlParser,
    ) {}

    /**
     * @return array<int, non-empty-string>
     *
     * @throws InvalidManifestException
     */
    public function validate(string $content): array
    {
        if ('' === trim($content)) {
            throw InvalidManifestException::createForEmptyContent();
        }

        try {
            $data = $this->yamlParser->parse($content);
        } catch (ParseException $parseException) {
            throw InvalidManifestException::createForInvalidYaml($content, $parseException);
        }

        if (false === is_array($data)) {
            throw InvalidManifestException::createForInvalidData($content);
        }

        $filteredTestPaths = [];
        foreach ($data as $testPath) {
            if (!is_string($testPath) || '' === $testPath) {
                throw InvalidManifestException::createForInvalidData($content);
            }

            $filteredTestPaths[] = $testPath;
        }

        return $filteredTestPaths;
    }
}
