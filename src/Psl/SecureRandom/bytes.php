<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Exception as PHPException;
use Psl;
use Psl\Str;
use Psl\Type;

use function random_bytes;

/**
 * Returns a cryptographically secure random bytes.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 * @throws Psl\Exception\InvariantViolationException If $length is negative.
 */
function bytes(int $length): string
{
    Psl\invariant($length >= 0, 'Expected a non-negative length.');
    if (0 === $length) {
        return '';
    }

    try {
        return random_bytes($length);
        // @codeCoverageIgnoreStart
    } catch (PHPException $e) {
        $code = $e->getCode();
        if (Type\string()->matches($code)) {
            $code = Str\to_int($code) ?? 0;
        }

        throw new Exception\InsufficientEntropyException('Unable to gather sufficient entropy.', $code, $e);
        // @codeCoverageIgnoreEnd
    }
}
