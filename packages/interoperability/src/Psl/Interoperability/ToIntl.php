<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface ToIntl<T>
{
    public function toIntl(): T;
}
