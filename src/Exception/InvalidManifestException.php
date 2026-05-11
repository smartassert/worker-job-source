<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Exception;

use SmartAssert\WorkerJobSource\Model\Manifest;
use Symfony\Component\Yaml\Exception\ParseException;

class InvalidManifestException extends \Exception
{
    public const int CODE_INVALID_YAML = 100;
    public const int CODE_INVALID_DATA = 200;
    public const int CODE_EMPTY = 300;

    public const int CODE_EMPTY_TEST_PATH_COLLECTION = 400;

    public function __construct(
        public readonly string $content = '',
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function createForInvalidYaml(string $content, ParseException $parseException): self
    {
        return new InvalidManifestException(
            $content,
            Manifest::FILENAME . ' content is not valid yaml.',
            self::CODE_INVALID_YAML,
            $parseException
        );
    }

    public static function createForInvalidData(string $content): self
    {
        return new InvalidManifestException(
            $content,
            Manifest::FILENAME . ' is not a list of strings.',
            self::CODE_INVALID_DATA
        );
    }

    public static function createForEmptyContent(): self
    {
        return new InvalidManifestException(
            '',
            Manifest::FILENAME . ' is empty.',
            self::CODE_EMPTY
        );
    }

    public static function createForEmptyTestPathCollection(string $content): self
    {
        return new InvalidManifestException(
            $content,
            Manifest::FILENAME . ' represents an empty collection.',
            self::CODE_EMPTY_TEST_PATH_COLLECTION
        );
    }
}
