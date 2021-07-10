<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

interface SequenceInterface
{
    /**
     * Generates the next pseudorandom number.
     */
    public function next(): int;
}
