<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @template T
 */
interface FromStdlib
{
    /**
     * @param T $value
     */
    public static function fromStdlib(mixed $value): static;
}
