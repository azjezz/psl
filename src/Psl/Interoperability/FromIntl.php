<?php

declare(strict_types=1);

namespace Psl\Interoperability;

/**
 * @template T
 */
interface FromIntl
{
    /**
     * @param T $value
     */
    public static function fromIntl(mixed $value): static;
}
