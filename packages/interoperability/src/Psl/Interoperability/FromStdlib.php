<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @api
 */
interface FromStdlib<T>
{
    public static function fromStdlib(T $value): static;
}
