<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface FromIntl<T = mixed>
{
    /**
     * @param T $value
     */
    public static function fromIntl(mixed $value): static;
}
