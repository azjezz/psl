<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface FromIntl<T>
{
    /**
     * @param T $value
     */
    public static function fromIntl(mixed $value): static;
}
