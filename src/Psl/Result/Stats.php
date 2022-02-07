<?php

declare(strict_types=1);

namespace Psl\Result;

/**
 * @psalm-immutable
 */
final class Stats
{
    private int $total = 0;
    private int $succeeded = 0;
    private int $failed = 0;

    public function apply(ResultInterface $result): self
    {
        $new = new self();
        $new->total = $this->total + 1;
        $new->succeeded = $result->isSucceeded() ? $this->succeeded + 1 : $this->succeeded;
        $new->failed = $result->isFailed() ? $this->failed + 1 : $this->failed;

        return $new;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function succeeded(): int
    {
        return $this->succeeded;
    }

    public function failed(): int
    {
        return $this->failed;
    }
}
