<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface ToStdlib<T = mixed>
{
    /**
     * @return T
     */
    public function toStdlib(): mixed;
}
