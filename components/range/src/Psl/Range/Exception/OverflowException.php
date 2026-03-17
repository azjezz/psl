<?php

declare(strict_types=1);

namespace Psl\Range\Exception;

use Psl\Exception;

use function sprintf;

final class OverflowException extends Exception\OverflowException implements ExceptionInterface
{
    public static function whileIterating(int $lowerBound): static
    {
        return new static(sprintf(
            'An overflow occurred while iterating over an infinite range from the `$lowerBound` of %d.',
            $lowerBound,
        ));
    }
}
