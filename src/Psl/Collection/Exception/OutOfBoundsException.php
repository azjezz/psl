<?php

declare(strict_types=1);

namespace Psl\Collection\Exception;

use Psl\Exception;
use Psl\Str;

final class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
    public static function for(string|int $offset): OutOfBoundsException
    {
        return new self(Str\format('Key (%s) was out-of-bounds.', $offset));
    }
}
