<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface FromStdlib<T>
{
    /**
     * @param T $value
     */
    public static function fromStdlib(mixed $value): static;
}
