<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @template T
 *
 * @api
 */
interface FromIntl
{
    /**
     * @param T $value
     */
    public static function fromIntl(mixed $value): static;
}
