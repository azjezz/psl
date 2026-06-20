<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface FromIntl<T>
{
    public static function fromIntl(T $value): static;
}
