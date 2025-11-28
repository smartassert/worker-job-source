<?php

declare(strict_types=1);

namespace SmartAssert\WorkerJobSource\Enum;

enum ManifestValidityState
{
    case VALID;
    case EMPTY;
    case INVALID_CONTENT;
}
