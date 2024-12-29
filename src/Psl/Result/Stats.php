<?php

declare(strict_types=1);

namespace Psl\Result;

/**
 * @psalm-immutable
 */
final readonly class Stats
{
    private int $total;
    private int $succeeded;
    private int $failed;

    /**
     * @psalm-mutation-free
     */
    public function __construct(int $total = 0, int $succeeded = 0, int $failed = 0)
    {
        $this->total = $total;
        $this->succeeded = $succeeded;
        $this->failed = $failed;
    }

    /**
     * @psalm-mutation-free
     */
    public function apply(ResultInterface $result): self
    {
        return new self(
            $this->total + 1,
            $result->isSucceeded() ? ($this->succeeded + 1) : $this->succeeded,
            $result->isFailed() ? ($this->failed + 1) : $this->failed,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * @psalm-mutation-free
     */
    public function succeeded(): int
    {
        return $this->succeeded;
    }

    /**
     * @psalm-mutation-free
     */
    public function failed(): int
    {
        return $this->failed;
    }
}
