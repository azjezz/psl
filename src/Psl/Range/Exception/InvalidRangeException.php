<?php

declare(strict_types=1);

namespace Psl\Range\Exception;

use Psl\Exception;
use Psl\Str;

final class InvalidRangeException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    public function __construct(
        string $message,
        private readonly int $lower_bound,
        private readonly int $upper_bound,
    ) {
        parent::__construct($message);
    }

    public static function lowerBoundIsGreaterThanUpperBound(
        int $lower_bound,
        int $upper_bound,
    ): self {
        return new self(
            Str\format(
                '`$lower_bound` (%d) must be less than or equal to `$upper_bound` (%d).',
                $lower_bound,
                $upper_bound,
            ),
            $lower_bound,
            $upper_bound,
        );
    }

    /**
     * Returns the lower bound.
     */
    public function getLowerBound(): int
    {
        return $this->lower_bound;
    }

    /**
     * Returns the upper bound.
     */
    public function getUpperBound(): int
    {
        return $this->upper_bound;
    }
}
