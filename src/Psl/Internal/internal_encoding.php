<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl;
use Psl\Type;
use Psl\Exception;

use function in_array;
use function mb_internal_encoding;
use function mb_list_encodings;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureFunctionCall
 *
 * @throws Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function internal_encoding(?string $encoding = null): string
{
    Psl\invariant(null === $encoding || in_array($encoding, mb_list_encodings(), true), 'Invalid encoding.');
    return $encoding ?? (Type\is_string($internal_encoding = mb_internal_encoding()) ? $internal_encoding : 'UTF-8');
}
