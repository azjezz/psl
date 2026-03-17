<?php

declare(strict_types=1);

namespace Psl\Binary;

use Stringable;

/**
 * Interface for binary data writers that buffer data in memory.
 *
 * Extends {@see WriterInterface} with the ability to retrieve the accumulated
 * binary string.
 */
interface BufferedWriterInterface extends WriterInterface, Stringable
{
    /**
     * Return the accumulated binary string.
     */
    public function toString(): string;
}
