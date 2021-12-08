<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl;
use Psl\Exception;

use function mb_internal_encoding;

/**
 * @pure
 *
 * @throws Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function internal_encoding(?string $encoding = null): string
{
    if (null !== $encoding) {
        Psl\invariant(is_encoding_valid($encoding), 'Invalid encoding.');

        return $encoding;
    }

    /**
     * @psalm-suppress ImpureFunctionCall
     *
     * @var string
     */
    return mb_internal_encoding() ?: 'UTF-8';
}
