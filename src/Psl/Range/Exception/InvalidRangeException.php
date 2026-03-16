<?php

declare(strict_types=1);

namespace Psl\Range\Exception;

use Psl\Exception;

use function sprintf;

final class InvalidRangeException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    public function __construct(
        string $message,
        private readonly int $lowerBound,
        private readonly int $upperBound,
    ) {
        parent::__construct($message);
    }

    public static function lowerBoundIsGreaterThanUpperBound(int $lowerBound, int $upperBound): self
    {
        return new self(
            sprintf('`$lowerBound` (%d) must be less than or equal to `$upperBound` (%d).', $lowerBound, $upperBound),
            $lowerBound,
            $upperBound,
        );
    }

    /**
     * Returns the lower bound.
     */
    public function getLowerBound(): int
    {
        return $this->lowerBound;
    }

    /**
     * Returns the upper bound.
     */
    public function getUpperBound(): int
    {
        return $this->upperBound;
    }
}
