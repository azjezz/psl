<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface ToStdlib<T>
{
    /**
     * @return T
     */
    public function toStdlib(): mixed;
}
