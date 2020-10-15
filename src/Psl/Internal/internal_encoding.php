<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl;
use Psl\Exception;
use Psl\Type;

use function mb_internal_encoding;

/**
 * @psalm-pure
 *
 * @throws Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function internal_encoding(?string $encoding = null): string
{
    Psl\invariant(null === $encoding || is_encoding_valid($encoding), 'Invalid encoding.');
    /** @psalm-suppress ImpureFunctionCall */
    return $encoding ?? (Type\is_string($internal_encoding = mb_internal_encoding()) ? $internal_encoding : 'UTF-8');
}
