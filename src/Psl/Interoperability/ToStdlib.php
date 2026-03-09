<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @template T
 */
interface ToStdlib
{
    /**
     * @return T
     */
    public function toStdlib(): mixed;
}
