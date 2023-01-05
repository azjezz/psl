<?php

declare(strict_types=1);

namespace Psl\Range\Exception;

use Psl\Exception;
use Psl\Str;

final class OverflowException extends Exception\OverflowException implements ExceptionInterface
{
    public static function whileIterating(int $lower_bound): static
    {
        return new static(Str\format(
            'An overflow occurred while iterating over an infinite range from the `$lower_bound` of %d.',
            $lower_bound,
        ));
    }
}
