<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for all Sets to enable access its values.
 *
 * @template Tv of array-key
 */
interface ConstSetAccess
{
    /**
     * Checks whether a value is in the current `Set`.
     *
     * @psalm-param Tv $m
     *
     * @psalm-return bool - `true` if the value is in the current `Set`; `false` otherwise
     */
    public function contains($m): bool;
}
