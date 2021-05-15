<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Exception as PHPException;
use Psl;
use Psl\Math;
use Psl\Str;
use Psl\Type;

use function random_int;

/**
 * Returns a cryptographically secure random integer in the range in the given range.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 * @throws Psl\Exception\InvariantViolationException If $min > $max.
 *
 * @psalm-external-mutation-free
 */
function int(int $min = Math\INT64_MIN, int $max = Math\INT64_MAX): int
{
    Psl\invariant($min <= $max, 'Expected $min (%d) to be less than or equal to $max (%d).', $min, $max);

    try {
        return random_int($min, $max);
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
