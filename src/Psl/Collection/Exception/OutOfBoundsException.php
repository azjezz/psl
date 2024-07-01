<?php

declare(strict_types=1);

namespace Psl\Collection\Exception;

use Psl\Exception;
use Psl\Str;

final class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
    /**
     * @psalm-mutation-free
     */
    public static function for(int|string $offset): OutOfBoundsException
    {
        return new self(Str\format('Key (%s) was out-of-bounds.', $offset));
    }
}
