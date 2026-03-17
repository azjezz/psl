<?php

declare(strict_types=1);

namespace Psl\Str\Exception;

use Psl\Exception;

use function sprintf;

/**
 * @mutation-free
 */
final class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
    /**
     * @pure
     */
    public static function for(int $offset): OutOfBoundsException
    {
        return new self(sprintf('Offset (%d) was out-of-bounds.', $offset));
    }
}
