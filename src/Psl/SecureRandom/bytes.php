<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Exception as PHPException;
use Psl\Str;
use Psl\Type;

use function random_bytes;

/**
 * Returns a cryptographically secure random bytes.
 *
 * @param int<0, max> $length The number of bytes to generate.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 *
 * @psalm-external-mutation-free
 */
function bytes(int $length): string
{
    if (0 === $length) {
        return '';
    }

    try {
        /** @var int<1, max> $length */
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
