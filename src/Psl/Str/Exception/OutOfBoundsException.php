<?php

declare(strict_types=1);

namespace Psl\Str\Exception;

use Psl\Exception;
use Psl\Str;

final class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
    public static function for(int $offset): OutOfBoundsException
    {
        return new self(Str\format('Offset (%d) was out-of-bounds.', $offset));
    }
}
