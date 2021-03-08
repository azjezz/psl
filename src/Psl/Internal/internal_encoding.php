<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl;
use Psl\Exception;
use Psl\Type;

use function mb_internal_encoding;

/**
 * @pure
 *
 * @throws Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function internal_encoding(?string $encoding = null): string
{
    Psl\invariant(null === $encoding || is_encoding_valid($encoding), 'Invalid encoding.');
    if (null !== $encoding) {
        return $encoding;
    }

    /**
     * @psalm-suppress ImpureFunctionCall
     */
    $internal_encoding = mb_internal_encoding();

    /**
     * @psalm-suppress ImpureFunctionCall - see https://github.com/azjezz/psl/issues/130
     * @psalm-suppress ImpureMethodCall - see https://github.com/azjezz/psl/issues/130
     */
    if (Type\string()->matches($internal_encoding)) {
        return $internal_encoding;
    }

    return 'UTF-8';
}
