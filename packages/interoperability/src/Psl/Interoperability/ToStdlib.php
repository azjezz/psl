<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface ToStdlib<T>
{
    public function toStdlib(): T;
}
