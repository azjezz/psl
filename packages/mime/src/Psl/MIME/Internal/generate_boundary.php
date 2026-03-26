<?php

declare(strict_types=1);

namespace Psl\MIME\Internal;

use Psl\MIME\Exception\EntropyException;
use Random\RandomException;
use Random\Randomizer;

/**
 * Generate a random multipart boundary string.
 *
 * @internal
 *
 * @throws EntropyException If the system cannot generate secure random values.
 *
 * @return non-empty-string A 24-character alphanumeric boundary.
 */
function generate_boundary(): string
{
    try {
        /** @var non-empty-string */
        return new Randomizer()->getBytesFromString('abcdefghijklmnopqrstuvwxyz0123456789', 24);
        // @codeCoverageIgnoreStart
    } catch (RandomException $e) {
        throw EntropyException::forInsufficientEntropy($e);
    }
    // @codeCoverageIgnoreEnd
}
