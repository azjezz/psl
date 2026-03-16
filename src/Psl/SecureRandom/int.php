<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Exception as PHPException;

use function is_string;
use function random_int;
use function sprintf;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Returns a cryptographically secure random integer in the given range.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 * @throws Exception\InvalidArgumentException If $min > $max.
 *
 * @psalm-external-mutation-free
 *
 * @return ($min is int<1, max> ? positive-int : ($min is int<0, max> ? non-negative-int : int))
 */
function int(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
{
    if ($max < $min) {
        throw new Exception\InvalidArgumentException(sprintf(
            'Expected $min (%d) to be less than or equal to $max (%d).',
            $min,
            $max,
        ));
    }

    if ($min === $max) {
        return $min;
    }

    try {
        return random_int($min, $max);
        // @codeCoverageIgnoreStart
    } catch (PHPException $e) {
        $code = $e->getCode();
        if (is_string($code)) {
            $code = (int) $code;
        }

        throw new Exception\InsufficientEntropyException('Unable to gather sufficient entropy.', $code, $e);
        // @codeCoverageIgnoreEnd
    }
}
